<?php

/**
 * Variation lines on the quote list, against stubbed WordPress:
 * - a variation is added with its attributes; "any" attributes must be a parent option;
 * - a disabled, out-of-stock or foreign-parent variation is refused;
 * - a hand-edited cookie key is dropped on read;
 * - the line name carries the chosen options.
 *
 * Run: php tests/quote-variations-check.php
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

    class WC_Product
    {
        public function __construct(public int $id, public string $type = 'simple', public int $parent = 0, public string $status = 'publish', public bool $stock = true, public array $attrs = [])
        {
        }
        public function get_id(): int { return $this->id; }
        public function get_meta(string $key): string { return 'yes'; }
        public function is_type(string $type): bool { return $this->type === $type; }
        public function get_parent_id(): int { return $this->parent; }
        public function get_status(): string { return $this->status; }
        public function get_post_password(): string { return ''; }
        public function get_name(): string { return 'Shirt'; }
        public function is_in_stock(): bool { return $this->stock; }
    }
    class WC_Product_Variable extends WC_Product
    {
        public function get_variation_attributes(): array { return $this->attrs; }
    }
    class WC_Product_Variation extends WC_Product
    {
        public function get_variation_attributes(): array { return $this->attrs; }
    }
    class WP_Term
    {
    }

    $products = [
        1 => new WC_Product_Variable(1, 'variable', 0, 'publish', true, ['Color' => ['red', 'blue'], 'Size' => ['S', 'L']]),
        // Fixed colour, any size.
        2 => new WC_Product_Variation(2, 'variation', 1, 'publish', true, ['attribute_color' => 'red', 'attribute_size' => '']),
        3 => new WC_Product_Variation(3, 'variation', 1, 'private', true, ['attribute_color' => 'blue', 'attribute_size' => 'S']),
        4 => new WC_Product_Variation(4, 'variation', 1, 'publish', false, ['attribute_color' => 'blue', 'attribute_size' => 'L']),
        5 => new WC_Product_Variable(5, 'variable', 0, 'draft', true, ['Color' => ['red']]),
        6 => new WC_Product_Variation(6, 'variation', 5, 'publish', true, ['attribute_color' => 'red']),
    ];

    function get_option(string $k, mixed $d = false): mixed { return ['mode' => 'selected']; }
    function wc_get_product(mixed $id): mixed { return $GLOBALS['products'][$id] ?? false; }
    function absint(mixed $v): int { return abs((int) $v); }
    function sanitize_title(string $s): string { return strtolower($s); }
    function taxonomy_exists(string $t): bool { return false; }
    function wc_attribute_label(string $n, mixed $p = ''): string { return ucfirst($n); }
    function wp_unslash(mixed $v): mixed { return $v; }
    function wp_json_encode(mixed $v): string { return (string) json_encode($v); }
    function is_ssl(): bool { return false; }
    function apply_filters(string $t, mixed $v, mixed ...$args): mixed { return $v; }

    require __DIR__ . '/../src/Service/QuoteList.php';

    $failures = [];
    $list     = new \Estimate\Service\QuoteList();

    // QV-1: an "any" attribute is taken from the choice; the fixed one from the variation.
    if (1 !== $list->add(2, 1, ['attribute_size' => 'L', 'attribute_color' => 'blue'])) {
        $failures[] = 'valid variation refused';
    }
    $lines = $list->lines();
    $want  = ['attribute_color' => 'red', 'attribute_size' => 'L'];
    if (1 !== count($lines) || $want !== $lines[0]['attributes'] || 2 !== $lines[0]['variation_id']) {
        $failures[] = 'stored line wrong: ' . json_encode($lines);
    }
    if ('Shirt (Color: red, Size: L)' !== ($lines[0]['name'] ?? '')) {
        $failures[] = 'line name does not carry the options: ' . ($lines[0]['name'] ?? '');
    }

    // QV-2: same choice again merges into the same line.
    $list->add(2, 2, ['attribute_size' => 'L']);
    if (1 !== count($list->lines()) || 3 !== $list->lines()[0]['qty']) {
        $failures[] = 'same variation did not merge';
    }

    // QV-3: refused lines.
    $cases = [
        'any value not a parent option' => [2, ['attribute_size' => 'XL']],
        'any value missing'             => [2, []],
        'disabled variation'            => [3, []],
        'out of stock variation'        => [4, []],
        'draft parent'                  => [6, []],
    ];
    foreach ($cases as $label => [$id, $chosen]) {
        if (0 !== $list->add($id, 1, $chosen)) {
            $failures[] = "added: $label";
        }
    }

    // QV-4: a hand-edited cookie is validated on read.
    $_COOKIE['estimate_quote_items'] = json_encode([
        '2|attribute_color=red&attribute_size=L' => 1,
        '2|attribute_color=blue&attribute_size=L' => 1, // Overrides a fixed attribute.
        '2|attribute_size=L&attribute_color=red'  => 1, // Not canonical order.
        '3|attribute_color=blue&attribute_size=S' => 1, // Disabled.
        '6|attribute_color=red'                   => 1, // Draft parent.
        '<script>'                                => 1,
    ]);
    $lines = (new \Estimate\Service\QuoteList())->lines();
    if (1 !== count($lines) || '2|attribute_color=red&attribute_size=L' !== $lines[0]['key']) {
        $failures[] = 'cookie lines not validated: ' . json_encode(array_column($lines, 'key'));
    }

    if ([] !== $failures) {
        fwrite(STDERR, "FAIL\n - " . implode("\n - ", $failures) . "\n");
        exit(1);
    }

    echo "OK: variation lines validated on add and on read\n";
}
