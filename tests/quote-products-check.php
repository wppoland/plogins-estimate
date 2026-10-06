<?php

/**
 * Storefront behaviour of quote products, against stubbed WordPress:
 * - Add to quote lands on the page holding [estimate_quote] and remembers it.
 * - The add-to-cart form block is replaced; the summary hook stays out of block templates.
 * - Quote products and their variations are not purchasable.
 * - The readme and PRO card make no false claims.
 *
 * Run: php tests/quote-products-check.php
 */

declare(strict_types=1);

namespace Estimate\Contract {
    interface HasHooks
    {
    }
}

namespace Estimate {
    const VERSION = 'test';
}

namespace {
    define('ABSPATH', __DIR__);
    define('DAY_IN_SECONDS', 86400);
    define('ESTIMATE_DIR', __DIR__ . '/../');

    class WP_Block
    {
        public array $context = [];
    }

    class WC_Product
    {
        public function __construct(public int $id, public array $meta = [], public string $type = 'simple', public int $parent = 0)
        {
        }
        public function get_id(): int { return $this->id; }
        public function get_meta(string $key): string { return $this->meta[$key] ?? ''; }
        public function is_type(string $type): bool { return $this->type === $type; }
        public function get_parent_id(): int { return $this->parent; }
    }

    $options  = ['estimate_settings' => ['mode' => 'selected'], 'estimate_quote_page_id' => 0];
    $products = [
        10 => new WC_Product(10, ['_estimate_quote_enabled' => 'yes']),
        12 => new WC_Product(12),
        11 => new WC_Product(11, ['_estimate_quote_enabled' => 'yes'], 'variable'),
        21 => new WC_Product(21, [], 'variation', 11),
    ];
    $hooked   = ['woocommerce_template_single_add_to_cart' => false];
    $added    = [];
    $contentPages   = [50, 77];
    $elementorPages = [];

    function get_option(string $k, mixed $d = false): mixed { return $GLOBALS['options'][$k] ?? $d; }
    function update_option(string $k, mixed $v): bool { $GLOBALS['options'][$k] = $v; return true; }
    function get_post_status(int $id): string|false { return 77 === $id ? 'publish' : false; }
    function get_posts(array $a): array { if (isset($a['meta_query'])) { return $GLOBALS['elementorPages']; } return 'page' === $a['post_type'] ? $GLOBALS['contentPages'] : []; }
    function get_post_field(string $f, int $id): string { return 77 === $id ? '<!-- wp:shortcode -->[estimate_quote]<!-- /wp:shortcode -->' : 'mentions [estimate_quote_old'; }
    function has_shortcode(string $c, string $tag): bool { return 1 === preg_match('/\[' . $tag . '[\s\]]/', $c); }
    function get_permalink(int $id): string { return "http://x/?page_id=$id"; }
    function wc_get_page_id(string $p): int { return 5; }
    function home_url(string $p = ''): string { return 'http://x/'; }
    function wc_get_product(mixed $id): mixed { return $GLOBALS['products'][$id] ?? false; }
    function get_the_ID(): int { return 0; }
    function has_action(string $h, string $cb): bool { return $GLOBALS['hooked'][$cb] ?? false; }
    function remove_action(string $h, mixed $cb, int $p = 10): bool { return true; }
    function add_action(string $h, mixed $cb, int $p = 10, int $a = 1): bool { $GLOBALS['added'][] = $h; return true; }
    function add_query_arg(array $args, string $url): string { return $url . '&' . http_build_query($args); }
    function wp_create_nonce(string $a): string { return 'n'; }
    function esc_url(string $s): string { return $s; }
    function esc_attr(string $s): string { return $s; }
    function esc_html(string $s): string { return $s; }
    function __(string $t, string $d = 'default'): string { return $t; }
    function absint(mixed $v): int { return abs((int) $v); }
    function sanitize_text_field(string $s): string { return $s; }
    function wp_unslash(mixed $v): mixed { return $v; }

    require __DIR__ . '/../src/Service/QuoteList.php';
    require __DIR__ . '/../src/Service/QuoteProducts.php';

    $qp       = new \Estimate\Service\QuoteProducts(new \Estimate\Service\QuoteList());
    $failures = [];

    // QT-1: the page with the shortcode is found (not 50, which only mentions a lookalike) and remembered.
    $html = $qp->maybeReplaceLoopButton('<a>Add to cart</a>', $products[10]);
    if (! str_contains($html, 'href="http://x/?page_id=77&')) {
        $failures[] = "add link does not target the quote page: $html";
    }
    if (77 !== $options['estimate_quote_page_id']) {
        $failures[] = 'quote page id not stored';
    }

    // QT-1b: a page built only with the Elementor widget is found through its page data.
    $contentPages   = [50];
    $elementorPages = [77];
    $options['estimate_quote_page_id'] = 0;
    $qp2 = new \Estimate\Service\QuoteProducts(new \Estimate\Service\QuoteList());
    if (! str_contains($qp2->maybeReplaceLoopButton('<a>Add to cart</a>', $products[10]), 'page_id=77&')) {
        $failures[] = 'Elementor-built quote page not found';
    }
    $contentPages   = [50, 77];
    $elementorPages = [];

    // QT-2: the block is swapped, and a blockified summary hook adds no second button.
    $block                     = new WP_Block();
    $block->context['postId']  = 10;
    $out = $qp->maybeReplaceBlock('<form class="cart">Add to cart</form>', [], $block);
    if (str_contains($out, 'form') || ! str_contains($out, 'estimate-add-to-quote')) {
        $failures[] = "add-to-cart block not replaced: $out";
    }
    $block->context['postId'] = 12;
    if ('<form/>' !== $qp->maybeReplaceBlock('<form/>', [], $block)) {
        $failures[] = 'non-quote product block was touched';
    }
    $GLOBALS['product'] = $products[10];
    $qp->maybeReplaceSingle();
    if ([] !== $added) {
        $failures[] = 'summary hook button added in a blockified template';
    }
    $hooked['woocommerce_template_single_add_to_cart'] = true;
    $qp->maybeReplaceSingle();
    if (['woocommerce_single_product_summary'] !== $added) {
        $failures[] = 'classic template lost its quote button';
    }

    // QT-3: not purchasable, variations judged by their parent.
    $cases = [[10, false], [21, false], [12, true]];
    foreach ($cases as [$id, $want]) {
        if ($want !== $qp->maybeBlockPurchase(true, $products[$id])) {
            $failures[] = "purchasable($id) should be " . var_export($want, true);
        }
    }
    $options['estimate_settings']['mode'] = 'all';
    if (false !== $qp->maybeBlockPurchase(true, $products[12])) {
        $failures[] = 'all mode leaves products purchasable';
    }

    // QT-4 / QT-5: claims.
    $readme = (string) file_get_contents(__DIR__ . '/../readme.txt');
    $upsell = (string) file_get_contents(__DIR__ . '/../config/pro-upsell.php');
    foreach (["isn't on WordPress.org", 'plus a Polish (pl_PL) translation'] as $claim) {
        if (str_contains($readme, $claim)) {
            $failures[] = "readme still claims: $claim";
        }
    }
    foreach (['planned', 'planowane'] as $claim) {
        if (str_contains($upsell, $claim)) {
            $failures[] = "PRO upsell still sells something $claim";
        }
    }

    if ([] !== $failures) {
        fwrite(STDERR, "FAIL\n - " . implode("\n - ", $failures) . "\n");
        exit(1);
    }

    echo "OK: quote page, block swap, purchasability and claims\n";
}
