<?php

/**
 * Shortlist wishlist to quote, against stubbed WordPress:
 * - the button lists only quote products and counts the rest in its note;
 * - no quote product on the wishlist means no button;
 * - the handler adds posted quote products once, refuses the rest and lands on the quote page;
 * - a bad nonce adds nothing;
 * - a one-item wishlist (do_action passes the bare product) still gets the button;
 * - a saved variation of a quote product counts as a quote item and is added once.
 *
 * Run: php tests/wishlist-quote-check.php
 */

declare(strict_types=1);

namespace Estimate\Contract {
    interface HasHooks
    {
    }
}

namespace {
    define('ABSPATH', __DIR__);
    define('DAY_IN_SECONDS', 86400);
    define('ESTIMATE_DIR', __DIR__ . '/../');

    class WC_Product
    {
        public function __construct(public int $id, public bool $quote = false)
        {
        }
        public function get_id(): int { return $this->id; }
        public function get_meta(string $key): string { return $this->quote ? 'yes' : ''; }
        public function is_type(string $type): bool { return 'simple' === $type; }
        public function get_status(): string { return 'publish'; }
        public function get_post_password(): string { return ''; }
    }

    class WC_Product_Variable extends WC_Product
    {
        public function is_type(string $type): bool { return 'variable' === $type; }
        /** @return array<string, list<string>> */
        public function get_variation_attributes(): array { return ['pa_color' => ['red', 'blue']]; }
    }

    class WC_Product_Variation extends WC_Product
    {
        public function __construct(public int $id, private int $parent)
        {
        }
        public function is_type(string $type): bool { return 'variation' === $type; }
        public function get_parent_id(): int { return $this->parent; }
        public function is_in_stock(): bool { return true; }
        /** @return array<string, string> */
        public function get_variation_attributes(): array { return ['attribute_pa_color' => 'blue']; }
    }

    final class Redirected extends \Exception
    {
    }

    $options  = ['estimate_settings' => ['mode' => 'selected'], 'estimate_quote_page_id' => 77];
    $products = [10 => new WC_Product(10, true), 11 => new WC_Product(11, true), 12 => new WC_Product(12), 20 => new WC_Product_Variable(20, true), 21 => new WC_Product_Variation(21, 20)];

    function get_option(string $k, mixed $d = false): mixed { return $GLOBALS['options'][$k] ?? $d; }
    function update_option(string $k, mixed $v): bool { return true; }
    function get_post_status(int $id): string|false { return 'publish'; }
    function get_permalink(int $id): string { return "http://x/?page_id=$id"; }
    function wc_get_product(mixed $id): mixed { return $GLOBALS['products'][$id] ?? false; }
    function apply_filters(string $t, mixed $v, mixed ...$args): mixed { return $v; }
    function esc_url(string $s): string { return $s; }
    function esc_attr(string $s): string { return $s; }
    function esc_html(string $s): string { return $s; }
    function esc_html_e(string $t, string $d = 'default'): void { echo $t; }
    function _n(string $s, string $p, int $n, string $d = 'default'): string { return 1 === $n ? $s : $p; }
    function absint(mixed $v): int { return abs((int) $v); }
    function sanitize_text_field(string $s): string { return $s; }
    function sanitize_title(string $s): string { return strtolower($s); }
    function wp_unslash(mixed $v): mixed { return $v; }
    function wp_json_encode(mixed $v): string|false { return json_encode($v); }
    function wp_nonce_field(string $a, string $n): void { echo '<input name="' . $n . '" value="ok" />'; }
    function wp_verify_nonce(string $n, string $a): bool { return 'ok' === $n && 'estimate_wishlist_quote' === $a; }
    function is_ssl(): bool { return false; }
    function wp_safe_redirect(string $url): void { throw new Redirected($url); }

    require __DIR__ . '/../src/Service/QuoteList.php';
    require __DIR__ . '/../src/Service/QuoteProducts.php';

    $failures = [];
    $render   = static function (array $items): string {
        ob_start();
        (new \Estimate\Service\QuoteProducts(new \Estimate\Service\QuoteList()))->renderWishlistButton($items);
        return (string) ob_get_clean();
    };

    $html = $render([$products[10], $products[12], $products[11]]);
    if (! str_contains($html, 'value="10"') || ! str_contains($html, 'value="11"') || str_contains($html, 'value="12"')) {
        $failures[] = "button lists the wrong products: $html";
    }
    if (! str_contains($html, '1 saved item is sold the usual way')) {
        $failures[] = 'skipped item not counted in the note';
    }
    if (! str_contains($html, 'action="http://x/?page_id=77"')) {
        $failures[] = 'form does not post to the quote page';
    }
    if (str_contains($render([$products[10]]), 'sold the usual way')) {
        $failures[] = 'note shown with nothing skipped';
    }
    if ('' !== $render([$products[12]])) {
        $failures[] = 'button shown with no quote product on the wishlist';
    }

    if (! str_contains($render([$products[10]]), 'value="10"')) {
        $failures[] = 'one quote item: no button';
    }
    $html = $render([$products[21], $products[12]]);
    if (! str_contains($html, 'value="21"') || ! str_contains($html, '1 saved item is sold the usual way')) {
        $failures[] = "saved quote variation not offered: $html";
    }

    $handle = static function (array $post): array {
        $_POST   = $post;
        $_COOKIE = [];
        $list    = new \Estimate\Service\QuoteList();
        $target  = '';
        try {
            (new \Estimate\Service\QuoteProducts($list))->handleWishlistQuote();
        } catch (Redirected $r) {
            $target = $r->getMessage();
        }
        return [array_values(array_filter([10, 11, 12], [$list, 'has'])), $target, $list];
    };

    [$lines, $target] = $handle(['estimate_wishlist_nonce' => 'ok', 'estimate_wishlist' => ['10', '12', '10', '11']]);
    if ([10, 11] !== $lines) {
        $failures[] = 'handler added ' . json_encode($lines) . ', expected [10,11]';
    }
    if ('http://x/?page_id=77' !== $target) {
        $failures[] = "handler did not land on the quote page: $target";
    }
    [, , $list] = $handle(['estimate_wishlist_nonce' => 'ok', 'estimate_wishlist' => ['21']]);
    $variationKey = \Estimate\Service\QuoteList::variationKey(21, ['attribute_pa_color' => 'blue']);
    if (! $list->has($variationKey)) {
        $failures[] = 'saved quote variation not added';
    }
    // Sending the wishlist again must not raise the variation line to 2.
    $_POST = ['estimate_wishlist_nonce' => 'ok', 'estimate_wishlist' => ['21']];
    try {
        (new \Estimate\Service\QuoteProducts($list))->handleWishlistQuote();
    } catch (Redirected $r) {
    }
    $raw = (new \ReflectionMethod($list, 'raw'))->invoke($list);
    if (1 !== ($raw[$variationKey] ?? 0)) {
        $failures[] = 'resubmitted wishlist raised the variation line to ' . json_encode($raw);
    }

    [$lines, $target] = $handle(['estimate_wishlist_nonce' => 'bad', 'estimate_wishlist' => ['10']]);
    if ([] !== $lines || '' !== $target) {
        $failures[] = 'bad nonce still added or redirected';
    }

    if ([] !== $failures) {
        fwrite(STDERR, "FAIL\n - " . implode("\n - ", $failures) . "\n");
        exit(1);
    }

    echo "OK: wishlist quote button, skip note, handler, one-item list and variations\n";
}
