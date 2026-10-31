<?php

declare(strict_types=1);

namespace Estimate\Service;

use Estimate\Contract\HasHooks;

defined('ABSPATH') || exit;

/**
 * Storefront behaviour for quote-enabled products.
 *
 * On products that are quote-enabled (either every product in "all" mode, or the
 * individually flagged ones in "selected" mode) this hides the price and the
 * add-to-cart button and renders an "Add to quote" button in their place, on
 * both single product pages and shop/category loops. Each "Add to quote" button
 * is a real link that adds the product and lands on the quote page, so the flow
 * works without any JavaScript.
 */
final class QuoteProducts implements HasHooks
{
    /** Product meta flag set by the merchant in "selected" mode. */
    public const META_ENABLED = QuoteList::META_ENABLED;

    private const OPTION = 'estimate_settings';
    private const NONCE  = 'estimate_quote';
    private const WISHLIST_NONCE = 'estimate_wishlist_quote';

    private ?string $quotePageUrl = null;

    public function __construct(private readonly QuoteList $list)
    {
    }

    public function registerHooks(): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        // Replace price + add-to-cart on quote-enabled products.
        add_filter('woocommerce_get_price_html', [$this, 'maybeHidePrice'], 100, 2);
        // Priority 2: after Shelfora's catalog mode (1), so when it has already
        // taken the add-to-cart slot Quotlet stays out and fills that slot through
        // catalog/add_to_cart_replacement below, giving one button, not two.
        add_action('woocommerce_single_product_summary', [$this, 'maybeReplaceSingle'], 2);
        add_filter('woocommerce_loop_add_to_cart_link', [$this, 'maybeReplaceLoopButton'], 100, 2);

        // Shelfora catalog mode: show "Add to quote" where it removes add to cart.
        add_filter('catalog/add_to_cart_replacement', [$this, 'catalogReplacement'], 20, 3);

        // Block themes render the add-to-cart form as a block, not through the summary hook.
        add_filter('render_block_woocommerce/add-to-cart-form', [$this, 'maybeReplaceBlock'], 100, 3);
        add_filter('render_block_woocommerce/add-to-cart-with-options', [$this, 'maybeReplaceBlock'], 100, 3);

        // Quote-only means no direct purchase, whatever the template shows.
        add_filter('woocommerce_is_purchasable', [$this, 'maybeBlockPurchase'], 100, 2);
        add_filter('woocommerce_variation_is_purchasable', [$this, 'maybeBlockPurchase'], 100, 2);

        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);

        // Handle the add-to-quote link.
        add_action('template_redirect', [$this, 'handleAddLink']);

        // Shortlist (wishlist), when active: quote the saved quote products in one go.
        add_action('shortlist_after_wishlist_items', [$this, 'renderWishlistButton']);
        add_action('template_redirect', [$this, 'handleWishlistQuote']);
    }

    /**
     * Whether a given product should be treated as quote-only.
     */
    public function isQuoteProduct(\WC_Product $product): bool
    {
        return $this->list->isQuoteProduct($product);
    }

    public function maybeHidePrice(mixed $price, mixed $product): mixed
    {
        if (! $product instanceof \WC_Product) {
            return $price;
        }

        if (! $this->isQuoteProduct($product)) {
            return $price;
        }

        if (empty($this->settings()['hide_price'])) {
            return $price;
        }

        return '';
    }

    /**
     * On single product pages, remove the add-to-cart form for quote products
     * and render our button instead.
     */
    public function maybeReplaceSingle(): void
    {
        global $product;

        if (! $product instanceof \WC_Product || ! $this->isQuoteProduct($product)) {
            return;
        }

        // In a blockified template WooCommerce has already unhooked the form and
        // renders it as a block, which maybeReplaceBlock() swaps instead.
        if (! has_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart')) {
            return;
        }

        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
        add_action('woocommerce_single_product_summary', [$this, 'renderSingleButton'], 30);
    }

    public function renderSingleButton(): void
    {
        global $product;

        if (! $product instanceof \WC_Product) {
            return;
        }

        echo $this->buttonHtml($product, true); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- buttonHtml escapes internally.
    }

    /**
     * Swap the add-to-cart form block for the "Add to quote" button.
     */
    public function maybeReplaceBlock(mixed $content, mixed $block = null, mixed $instance = null): mixed
    {
        $productId = $instance instanceof \WP_Block ? (int) ($instance->context['postId'] ?? 0) : 0;
        $product   = wc_get_product($productId > 0 ? $productId : get_the_ID());

        if (! $product instanceof \WC_Product || ! $this->isQuoteProduct($product)) {
            return $content;
        }

        return $this->buttonHtml($product, true);
    }

    /**
     * Shelfora catalog mode removed add to cart: on a quote product the
     * "Add to quote" button takes its place, so the shopper still has a way
     * to ask for the product. Other products keep Shelfora's own replacement.
     */
    public function catalogReplacement(mixed $html, mixed $product = null, mixed $context = 'single'): mixed
    {
        if (! $product instanceof \WC_Product || ! $this->isQuoteProduct($product)) {
            return $html;
        }

        return $this->buttonHtml($product, 'loop' !== $context);
    }

    /**
     * Quote products (and the variations of one) cannot be bought directly.
     */
    public function maybeBlockPurchase(mixed $purchasable, mixed $product): mixed
    {
        if (! $purchasable || ! $product instanceof \WC_Product) {
            return $purchasable;
        }

        if ($product->is_type('variation')) {
            $product = wc_get_product($product->get_parent_id());
        }

        return ! ($product instanceof \WC_Product && $this->isQuoteProduct($product));
    }

    /**
     * In product loops, swap the add-to-cart link for an "Add to quote" link.
     */
    public function maybeReplaceLoopButton(mixed $html, mixed $product): mixed
    {
        if (! $product instanceof \WC_Product || ! $this->isQuoteProduct($product)) {
            return $html;
        }

        return $this->buttonHtml($product, false);
    }

    /**
     * Build the "Add to quote" button markup. All dynamic values are escaped.
     */
    private function buttonHtml(\WC_Product $product, bool $single): string
    {
        if (function_exists('wp_enqueue_style')) {
            wp_enqueue_style('estimate');
        }
        $label = $this->buttonLabel();
        $added = $this->list->has($product->get_id());

        $classes = 'button wp-element-button estimate-add-to-quote';
        if (! $single) {
            $classes .= ' add_to_cart_button';
        }
        if ($added) {
            $classes .= ' estimate-added';
        }

        $href = add_query_arg(
            [
                'estimate_add'   => $product->get_id(),
                'estimate_nonce' => wp_create_nonce(self::NONCE),
            ],
            $this->quotePageUrl(),
        );

        if ($single && $product instanceof \WC_Product_Variable) {
            return $this->variationFormHtml($product, $href, $classes, $label);
        }

        return sprintf(
            '<a href="%1$s" class="%2$s">%3$s</a>',
            esc_url($href),
            esc_attr($classes),
            esc_html($added ? __('In your quote', 'quotlet') : $label),
        );
    }

    /**
     * On a variable product page: one select per variation attribute and the
     * add button. Posts to the same add URL; handleAddLink() resolves the
     * variation. Works without JavaScript.
     */
    private function variationFormHtml(\WC_Product_Variable $product, string $href, string $classes, string $label): string
    {
        ob_start();

        echo '<form method="post" class="estimate-variation-form" action="' . esc_url($href) . '">';

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only notice flag, compared against '1'.
        if (isset($_GET['estimate_unavailable']) && '1' === sanitize_text_field(wp_unslash($_GET['estimate_unavailable']))) {
            echo '<p class="estimate-variation-form__notice" role="alert">' . esc_html__('That combination is not available. Please choose different options.', 'quotlet') . '</p>';
        }

        foreach ($product->get_variation_attributes() as $name => $options) {
            $name = (string) $name;
            $id   = 'estimate-' . $product->get_id() . '-' . sanitize_title($name);

            echo '<p class="estimate-variation-form__field"><label for="' . esc_attr($id) . '">' . esc_html(wc_attribute_label($name, $product)) . '</label>';

            ob_start();
            wc_dropdown_variation_attribute_options([
                'options'   => $options,
                'attribute' => $name,
                'product'   => $product,
                'id'        => $id,
            ]);
            // Every option must be chosen before the form can be sent.
            echo str_replace('<select ', '<select required ', (string) ob_get_clean()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce escapes the dropdown markup.

            echo '</p>';
        }

        printf(
            '<button type="submit" class="%1$s">%2$s</button></form>',
            esc_attr($classes),
            esc_html($label),
        );

        return (string) ob_get_clean();
    }

    /**
     * Add the product from the "Add to quote" link, then redirect to the quote
     * page.
     */
    public function handleAddLink(): void
    {
        if (! isset($_GET['estimate_add'], $_GET['estimate_nonce'])) {
            return;
        }

        $nonce = sanitize_text_field(wp_unslash($_GET['estimate_nonce']));

        if (! wp_verify_nonce($nonce, self::NONCE)) {
            return;
        }

        $productId = absint(wp_unslash($_GET['estimate_add']));
        $product   = $productId > 0 ? wc_get_product($productId) : null;

        if (! $product instanceof \WC_Product || ! $this->list->accepts($product)) {
            wp_safe_redirect($this->quotePageUrl());
            exit;
        }

        $chosen = $product instanceof \WC_Product_Variable ? $this->postedAttributes($product) : [];

        if (! $product instanceof \WC_Product_Variable || [] === $chosen) {
            // A product, or a variable product added from a listing.
            $this->list->add($productId, 1);
        } elseif ($this->list->add($this->postedVariationId($product, $chosen), 1, $chosen) < 1) {
            wp_safe_redirect(add_query_arg('estimate_unavailable', '1', (string) get_permalink($productId)));
            exit;
        }

        wp_safe_redirect($this->quotePageUrl());
        exit;
    }

    /**
     * Under the Shortlist wishlist: a button that adds the saved quote products
     * to the quote list, and a note naming how many saved items it leaves out.
     *
     * @param mixed $products Shortlist passes list<\WC_Product>.
     */
    public function renderWishlistButton(mixed $products): void
    {
        if (function_exists('wp_enqueue_style')) {
            wp_enqueue_style('estimate');
        }
        $ids     = [];
        $skipped = 0;

        // do_action() hands a one-item list over as the bare product.
        $products = $products instanceof \WC_Product ? [$products] : (is_array($products) ? $products : []);

        foreach ($products as $product) {
            if ($product instanceof \WC_Product && null !== $this->wishlistKey($product)) {
                $ids[] = $product->get_id();
            } else {
                ++$skipped;
            }
        }

        if ([] === $ids) {
            return;
        }
        ?>
        <form class="estimate-wishlist-quote" method="post" action="<?php echo esc_url($this->quotePageUrl()); ?>">
            <?php wp_nonce_field(self::WISHLIST_NONCE, 'estimate_wishlist_nonce'); ?>
            <?php foreach ($ids as $id) : ?>
                <input type="hidden" name="estimate_wishlist[]" value="<?php echo esc_attr((string) $id); ?>" />
            <?php endforeach; ?>
            <button type="submit" class="button alt"><?php esc_html_e('Request a quote for these items', 'quotlet'); ?></button>
            <?php if ($skipped > 0) : ?>
                <p class="estimate-wishlist-quote__note">
                    <?php
                    echo esc_html(sprintf(
                        /* translators: %d: number of saved wishlist items that are not quote products. */
                        _n(
                            '%d saved item is sold the usual way and is not added to the quote.',
                            '%d saved items are sold the usual way and are not added to the quote.',
                            $skipped,
                            'quotlet'
                        ),
                        $skipped
                    ));
                    ?>
                </p>
            <?php endif; ?>
        </form>
        <?php
    }

    /**
     * Add the products posted from the wishlist button, then land on the quote
     * page. add() refuses anything that is not a quote product, so a tampered
     * id list adds nothing the shopper could not add with the button itself.
     */
    public function handleWishlistQuote(): void
    {
        if (! isset($_POST['estimate_wishlist'], $_POST['estimate_wishlist_nonce'])) {
            return;
        }

        $nonce = sanitize_text_field(wp_unslash($_POST['estimate_wishlist_nonce']));

        if (! wp_verify_nonce($nonce, self::WISHLIST_NONCE)) {
            return;
        }

        $ids = array_map('absint', (array) wp_unslash($_POST['estimate_wishlist']));

        foreach (array_unique($ids) as $id) {
            $product = $id > 0 ? wc_get_product($id) : null;
            $key     = $product instanceof \WC_Product ? $this->wishlistKey($product) : null;

            if (null !== $key && ! $this->list->has($key)) {
                $this->list->add($id, 1);
            }
        }

        wp_safe_redirect($this->quotePageUrl());
        exit;
    }

    /**
     * The quote line key a saved wishlist item would take, or null when it
     * cannot go on the quote. Shortlist saves the variation a shopper picked;
     * one that fixes every attribute can go on the quote as it is, one with an
     * "any" attribute needs a choice the wishlist does not hold.
     */
    private function wishlistKey(\WC_Product $product): int|string|null
    {
        if ($product->is_type('variation')) {
            $attributes = $this->list->variationAttributes($product, []);

            return null === $attributes ? null : QuoteList::variationKey($product->get_id(), $attributes);
        }

        return $this->list->accepts($product) ? $product->get_id() : null;
    }

    /**
     * Attribute choices posted by the variation form, attribute_* => value,
     * limited to the parent's variation attributes. Called after the nonce
     * check in handleAddLink().
     *
     * @return array<string, string>
     */
    private function postedAttributes(\WC_Product_Variable $product): array
    {
        $chosen = [];

        foreach (array_keys($product->get_variation_attributes()) as $name) {
            $field = 'attribute_' . sanitize_title((string) $name);

            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in handleAddLink().
            if (isset($_POST[$field]) && is_string($_POST[$field])) {
                // wc_clean() is sanitize_text_field(), as WooCommerce's own add-to-cart reads attributes.
                $chosen[$field] = wc_clean(wp_unslash($_POST[$field])); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            }
        }

        return $chosen;
    }

    /**
     * The variation the shopper picked: the posted variation_id when it
     * belongs to this parent, otherwise the one matching the attributes.
     * QuoteList::add() then checks the variation is enabled, in stock and
     * that its attributes are valid for the parent. 0 when nothing matches.
     *
     * @param array<string, string> $chosen
     */
    private function postedVariationId(\WC_Product_Variable $product, array $chosen): int
    {
        $posted = isset($_POST['variation_id']) ? absint(wp_unslash($_POST['variation_id'])) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in handleAddLink().

        if ($posted > 0) {
            $variation = wc_get_product($posted);

            return $variation instanceof \WC_Product_Variation && $variation->get_parent_id() === $product->get_id() ? $posted : 0;
        }

        /** @var \WC_Product_Data_Store_CPT $store */
        $store = \WC_Data_Store::load('product');

        return (int) $store->find_matching_product_variation($product, $chosen);
    }

    public function enqueueAssets(): void
    {
        wp_register_style(
            'estimate',
            ESTIMATE_URL . 'assets/css/estimate.css',
            [],
            \Estimate\VERSION,
        );

        wp_register_script(
            'estimate',
            ESTIMATE_URL . 'assets/js/estimate.js',
            [],
            \Estimate\VERSION,
            true,
        );

        // Load in the head only where buttons or the quote form are certain to
        // render; anywhere else (a product block on a plain page) the renderer
        // enqueues the stylesheet itself, so plain pages and posts stay clean.
        $quotePage = is_singular() && has_shortcode((string) get_post_field('post_content'), 'estimate_quote');
        if ($quotePage || (function_exists('is_woocommerce') && (is_woocommerce() || is_cart() || is_checkout()))) {
            wp_enqueue_style('estimate');
        }
        if ($quotePage) {
            wp_enqueue_script('estimate');
        }
    }

    private function buttonLabel(): string
    {
        $custom = trim((string) ($this->settings()['button_text'] ?? ''));

        return '' !== $custom ? $custom : __('Add to quote', 'quotlet');
    }

    /**
     * URL of the page holding the [estimate_quote] shortcode. Nothing asks the
     * merchant for that page, so it is found by its shortcode and remembered.
     * Falls back to the shop page (then home) so the no-JS flow always has
     * somewhere to land.
     */
    private function quotePageUrl(): string
    {
        if (null !== $this->quotePageUrl) {
            return $this->quotePageUrl;
        }

        $pageId = (int) get_option('estimate_quote_page_id', 0);

        if ($pageId <= 0 || 'publish' !== get_post_status($pageId)) {
            $pageId = $this->findQuotePage();

            if ($pageId > 0) {
                update_option('estimate_quote_page_id', $pageId);
            }
        }

        if ($pageId > 0) {
            return $this->quotePageUrl = (string) get_permalink($pageId);
        }

        $shop = wc_get_page_id('shop');

        return $this->quotePageUrl = $shop > 0 ? (string) get_permalink($shop) : home_url('/');
    }

    /**
     * First published page that carries the [estimate_quote] shortcode, in its
     * content or as the Elementor widget.
     */
    private function findQuotePage(): int
    {
        $ids = get_posts([
            'post_type'        => 'page',
            'post_status'      => 'publish',
            's'                => '[estimate_quote',
            'fields'           => 'ids',
            'posts_per_page'   => 10,
            'orderby'          => 'ID',
            'order'            => 'ASC',
        ]);

        foreach ($ids as $id) {
            if (has_shortcode((string) get_post_field('post_content', $id), 'estimate_quote')) {
                return (int) $id;
            }
        }

        // A page built with the Elementor widget keeps the shortcode out of
        // post_content; Elementor stores the widget in its own page data.
        $ids = get_posts([
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'fields'         => 'ids',
            'posts_per_page' => 1,
            'orderby'        => 'ID',
            'order'          => 'ASC',
            'meta_query'     => [[ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- one-off lookup, cached in an option.
                'key'     => '_elementor_data',
                'value'   => '"widgetType":"estimate_quote"',
                'compare' => 'LIKE',
            ]],
        ]);

        return (int) ($ids[0] ?? 0);
    }

    private function isEnabled(): bool
    {
        return (bool) ($this->settings()['enabled'] ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    private function settings(): array
    {
        $stored = get_option(self::OPTION, []);

        if (! is_array($stored)) {
            $stored = [];
        }

        /** @var array<string, mixed> $defaults */
        $defaults = require ESTIMATE_DIR . 'config/defaults.php';

        return array_merge($defaults, $stored);
    }
}
