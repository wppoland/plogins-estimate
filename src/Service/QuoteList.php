<?php

declare(strict_types=1);

namespace Estimate\Service;

use Estimate\Contract\HasHooks;

defined('ABSPATH') || exit;

/**
 * Per-visitor quote list.
 *
 * Items are stored in a single cookie as a compact map of line key => quantity,
 * where the key is a product ID or, for a variation, variationKey().
 * No personal data is kept and nothing is written server-side until the visitor
 * submits the request form, so this works for logged-out shoppers and is cache
 * friendly. The cookie is read on demand and written via the standard headers,
 * so the list survives navigation for the configured lifetime.
 */
final class QuoteList implements HasHooks
{
    private const COOKIE = 'estimate_quote_items';

    /** Product meta flag set by the merchant in "selected" mode. */
    public const META_ENABLED = '_estimate_quote_enabled';

    /** Cookie lifetime in seconds (30 days). */
    private const LIFETIME = 30 * DAY_IN_SECONDS;

    /** Hard cap on distinct line items to keep the cookie small and sane. */
    private const MAX_ITEMS = 100;

    /**
     * In-request cache so repeated reads (and writes within the same request)
     * stay consistent even though the cookie superglobal is only sent next load.
     *
     * @var array<array-key, int>|null
     */
    private ?array $items = null;

    public function registerHooks(): void
    {
        // Nothing to hook: the list is read/written on demand. Implementing
        // HasHooks keeps it uniform with the rest of the service registry.
    }

    /**
     * Current lines a visitor may request a quote for. The cookie is
     * client-controlled, so every line is validated again on read: products
     * must pass accepts(), variations variationAttributes().
     *
     * product_id is the requested product (the variation id for a variation
     * line), variation_id is 0 for a simple line.
     *
     * @return list<array{key: string, product_id: int, variation_id: int, attributes: array<string, string>, name: string, qty: int}>
     */
    public function lines(): array
    {
        $lines = [];

        foreach ($this->raw() as $key => $qty) {
            $line = $this->resolve((string) $key);

            if (null !== $line) {
                $lines[] = $line + ['key' => (string) $key, 'qty' => $qty];
            }
        }

        return $lines;
    }

    /**
     * Whether a product is quote-enabled (every product in "all" mode, flagged
     * ones in "selected" mode).
     */
    public function isQuoteProduct(\WC_Product $product): bool
    {
        $stored = get_option('estimate_settings', []);
        $mode   = is_array($stored) ? (string) ($stored['mode'] ?? 'selected') : 'selected';

        $enabled = 'all' === $mode || 'yes' === $product->get_meta(self::META_ENABLED);

        return (bool) apply_filters('estimate/is_quote_product', $enabled, $product);
    }

    /**
     * Whether a product may be on a visitor's list: published, not password
     * protected, and quote-enabled.
     */
    public function accepts(\WC_Product $product): bool
    {
        // A variation is judged through variationAttributes(), which checks
        // its parent: on its own status alone it could print the name of a
        // draft, private or password-protected parent.
        return ! $product->is_type('variation')
            && 'publish' === $product->get_status()
            && '' === $product->get_post_password()
            && $this->isQuoteProduct($product);
    }

    /**
     * The full attribute set of a variation a visitor may request, or null.
     *
     * The variation must be enabled and in stock (unless the
     * estimate/variation_requires_stock filter returns false), and belong to a variable
     * parent that accepts() allows. Attributes the variation fixes are taken
     * from the variation; "any" attributes come from $chosen and must be one
     * of the parent's options. ("Purchasable" is not asked: quote products
     * are made unpurchasable on purpose.)
     *
     * @param array<string, string> $chosen attribute_* => value.
     * @return array<string, string>|null attribute_* => value, sorted by key.
     */
    public function variationAttributes(\WC_Product $variation, array $chosen): ?array
    {
        if (! $variation instanceof \WC_Product_Variation
            || 'publish' !== $variation->get_status()
            || (! $variation->is_in_stock() && (bool) apply_filters('estimate/variation_requires_stock', true, $variation))
        ) {
            return null;
        }

        $parent = wc_get_product($variation->get_parent_id());

        if (! $parent instanceof \WC_Product_Variable || ! $this->accepts($parent)) {
            return null;
        }

        $options = [];
        foreach ($parent->get_variation_attributes() as $name => $values) {
            $options['attribute_' . sanitize_title((string) $name)] = array_map('strval', (array) $values);
        }

        $attributes = [];
        foreach ($variation->get_variation_attributes() as $key => $value) {
            $value = (string) $value;

            if ('' === $value) {
                $value = (string) ($chosen[$key] ?? '');

                if (! in_array($value, $options[$key] ?? [], true)) {
                    return null;
                }
            }

            $attributes[(string) $key] = $value;
        }

        ksort($attributes);

        return $attributes;
    }

    /**
     * @param int|string $key A product id, or a variationKey() line key.
     */
    public function has(int|string $key): bool
    {
        return isset($this->raw()[$key]);
    }

    /**
     * Add a product or a variation (or increase its quantity). $attributes
     * fills a variation's "any" attributes. Returns the new line quantity, 0
     * when the line is refused.
     *
     * @param array<string, string> $attributes attribute_* => value.
     */
    public function add(int $productId, int $qty = 1, array $attributes = []): int
    {
        $productId = absint($productId);
        $qty       = max(1, $qty);
        $product   = $productId > 0 ? wc_get_product($productId) : null;

        if (! $product instanceof \WC_Product) {
            return 0;
        }

        if ($product->is_type('variation')) {
            $attributes = $this->variationAttributes($product, $attributes);

            if (null === $attributes) {
                return 0;
            }

            $key = self::variationKey($productId, $attributes);
        } elseif ($this->accepts($product)) {
            $key = (string) $productId;
        } else {
            return 0;
        }

        $items = $this->raw();

        if (! isset($items[$key]) && count($items) >= self::MAX_ITEMS) {
            return 0;
        }

        $items[$key] = ($items[$key] ?? 0) + $qty;

        $this->persist($items);

        return $items[$key];
    }

    public function setQuantity(string $key, int $qty): void
    {
        $items = $this->raw();

        if (! isset($items[$key])) {
            return;
        }

        if ($qty <= 0) {
            unset($items[$key]);
        } else {
            $items[$key] = $qty;
        }

        $this->persist($items);
    }

    public function remove(string $key): void
    {
        $items = $this->raw();
        unset($items[$key]);
        $this->persist($items);
    }

    public function clear(): void
    {
        $this->persist([]);
    }

    /**
     * Line key of a variation: "<variation id>|<query string of its attributes>".
     * A simple product's key is its id.
     *
     * @param array<string, string> $attributes
     */
    public static function variationKey(int $variationId, array $attributes): string
    {
        ksort($attributes);

        return $variationId . '|' . http_build_query($attributes);
    }

    /**
     * Validate one stored line key into its product data, or null.
     *
     * @return array{product_id: int, variation_id: int, attributes: array<string, string>, name: string}|null
     */
    private function resolve(string $key): ?array
    {
        if (ctype_digit($key)) {
            $product = wc_get_product((int) $key);

            if (! $product instanceof \WC_Product || ! $this->accepts($product)) {
                return null;
            }

            return ['product_id' => (int) $key, 'variation_id' => 0, 'attributes' => [], 'name' => $product->get_name()];
        }

        if (1 !== preg_match('/^(\d+)\|(.*)$/s', $key, $m)) {
            return null;
        }

        $variation = wc_get_product((int) $m[1]);
        parse_str($m[2], $chosen);
        $chosen = array_filter($chosen, 'is_string');

        if (! $variation instanceof \WC_Product) {
            return null;
        }

        $attributes = $this->variationAttributes($variation, $chosen);

        // A key that is not in canonical form (hand-edited cookie) is dropped
        // rather than shown as a second line for the same variation.
        if (null === $attributes || self::variationKey((int) $m[1], $attributes) !== $key) {
            return null;
        }

        $parent = wc_get_product($variation->get_parent_id());

        if (! $parent instanceof \WC_Product) {
            return null;
        }

        return [
            'product_id'   => (int) $m[1],
            'variation_id' => (int) $m[1],
            'attributes'   => $attributes,
            'name'         => $parent->get_name() . ' (' . self::describe($parent, $attributes) . ')',
        ];
    }

    /**
     * "Color: Red, Size: L" for a variation's attributes.
     *
     * @param array<string, string> $attributes attribute_* => value.
     */
    public static function describe(\WC_Product $parent, array $attributes): string
    {
        $parts = [];

        foreach ($attributes as $key => $value) {
            $name = substr($key, strlen('attribute_'));

            if (taxonomy_exists($name)) {
                $term  = get_term_by('slug', $value, $name);
                $value = $term instanceof \WP_Term ? $term->name : $value;
            }

            $parts[] = wc_attribute_label($name, $parent) . ': ' . $value;
        }

        return implode(', ', $parts);
    }

    /**
     * Raw stored map (line key => quantity), unfiltered by product validity.
     *
     * @return array<array-key, int>
     */
    private function raw(): array
    {
        if (null !== $this->items) {
            return $this->items;
        }

        $this->items = [];

        // Not run through sanitize_text_field(): it strips the %XX octets a
        // variation key carries. The value is JSON; every key is checked
        // below and every line again in lines().
        $raw = isset($_COOKIE[self::COOKIE])
            ? (string) wp_unslash($_COOKIE[self::COOKIE]) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- decoded and validated per key below.
            : '';

        if ('' === $raw) {
            return $this->items;
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            return $this->items;
        }

        foreach ($decoded as $key => $qty) {
            $key = (string) $key;
            $qty = is_scalar($qty) ? absint($qty) : 0;

            if ($qty > 0 && 1 === preg_match('/^[1-9]\d*(\|[A-Za-z0-9_.%=&+~-]*)?$/', $key)) {
                $this->items[$key] = $qty;
            }
        }

        return $this->items;
    }

    /**
     * @param array<array-key, int> $items
     */
    private function persist(array $items): void
    {
        $items        = array_slice($items, 0, self::MAX_ITEMS, true);
        $this->items  = $items;
        $value        = '' === ($json = (string) wp_json_encode($items)) ? '' : $json;

        // Don't try to send headers if output already started (e.g. shortcode in
        // body). The in-request cache still keeps this request consistent.
        if (headers_sent()) {
            return;
        }

        $path   = defined('COOKIEPATH') && '' !== COOKIEPATH ? COOKIEPATH : '/';
        $domain = defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '';

        if ([] === $items) {
            setcookie(self::COOKIE, '', time() - DAY_IN_SECONDS, $path, $domain, is_ssl(), true);
            return;
        }

        setcookie(
            self::COOKIE,
            $value,
            time() + self::LIFETIME,
            $path,
            $domain,
            is_ssl(),
            true,
        );
    }
}
