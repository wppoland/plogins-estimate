<?php

/**
 * My Account quotes are matched on the stored user ID only, never on the
 * account email.
 *
 * WooCommerce does not verify an email change in My Account, so a customer
 * who set a guest's address on their own account was shown every quote that
 * guest had filed (name, phone, company, message, items) by estimate-pro's
 * Quotes endpoint, which reads customerIds() and customerOwns().
 *
 * Run: php tests/quote-customer-ids-check.php
 */

declare(strict_types=1);

namespace Estimate\Contract {
    interface HasHooks
    {
    }
}

namespace {
    define('ABSPATH', __DIR__);

    // Quote 10 was filed by guest victim@example.com, quote 11 by user 5.
    $GLOBALS['quotes'] = [
        10 => ['_estimate_user_id' => '', '_estimate_email' => 'victim@example.com'],
        11 => ['_estimate_user_id' => '5', '_estimate_email' => 'five@example.com'],
    ];

    function sanitize_email($email) { return (string) $email; }
    function apply_filters($hook, $value) { return $value; }

    function get_posts(array $args): array
    {
        $clauses = $args['meta_query'] ?? [];
        if (isset($args['meta_key'])) {
            $clauses[] = ['key' => $args['meta_key'], 'value' => $args['meta_value']];
        }
        unset($clauses['relation']);

        $ids = [];
        foreach ($GLOBALS['quotes'] as $id => $meta) {
            foreach ($clauses as $clause) {
                if (($meta[$clause['key']] ?? null) === $clause['value']) {
                    $ids[] = $id;
                    break;
                }
            }
        }

        return $ids;
    }

    require __DIR__ . '/../src/PostType/QuoteRequest.php';

    $requests = (new ReflectionClass(\Estimate\PostType\QuoteRequest::class))->newInstanceWithoutConstructor();
    $failures = [];

    // User 9 changed their account email to the guest's address.
    if ($requests->customerIds(9, 'victim@example.com') !== []) {
        $failures[] = 'another account with the guest\'s email sees the guest quote';
    }
    if ($requests->customerOwns(10, 9, 'victim@example.com')) {
        $failures[] = 'another account with the guest\'s email owns the guest quote';
    }
    if ($requests->customerIds(5, 'five@example.com') !== [11]) {
        $failures[] = 'the owner no longer sees their own quote';
    }

    if ($failures !== []) {
        fwrite(STDERR, "quote-customer-ids-check: FAILED\n  " . implode("\n  ", $failures) . "\n");
        exit(1);
    }

    echo "quote-customer-ids-check: OK\n";
}
