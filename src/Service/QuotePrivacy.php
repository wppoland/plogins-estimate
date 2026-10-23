<?php

declare(strict_types=1);

namespace Estimate\Service;

use Estimate\Contract\HasHooks;
use Estimate\PostType\QuoteRequest;

defined('ABSPATH') || exit;

/**
 * Personal data exporter and eraser (Tools > Export / Erase Personal Data)
 * for stored quote requests. A request belongs to an email address when it
 * was sent with that address or by the account that owns it.
 */
final class QuotePrivacy implements HasHooks
{
    /** Requests handled per exporter / eraser page. */
    private const PER_PAGE = 20;

    public function __construct(private readonly QuoteRequest $requests)
    {
    }

    public function registerHooks(): void
    {
        add_filter('wp_privacy_personal_data_exporters', [$this, 'registerExporter']);
        add_filter('wp_privacy_personal_data_erasers', [$this, 'registerEraser']);
    }

    /**
     * @param array<string, mixed> $exporters
     * @return array<string, mixed>
     */
    public function registerExporter(array $exporters): array
    {
        $exporters['quotlet'] = [
            'exporter_friendly_name' => __('Quote requests', 'quotlet'),
            'callback'               => [$this, 'export'],
        ];

        return $exporters;
    }

    /**
     * @param array<string, mixed> $erasers
     * @return array<string, mixed>
     */
    public function registerEraser(array $erasers): array
    {
        $erasers['quotlet'] = [
            'eraser_friendly_name' => __('Quote requests', 'quotlet'),
            'callback'             => [$this, 'erase'],
        ];

        return $erasers;
    }

    /**
     * @return array{data: list<array<string, mixed>>, done: bool}
     */
    public function export(string $email, int $page = 1): array
    {
        $ids  = $this->ids($email, max(1, $page));
        $data = [];

        foreach ($ids as $id) {
            $fields = [
                __('Name', 'quotlet')    => (string) get_post_meta($id, QuoteRequest::META_NAME, true),
                __('Email', 'quotlet')   => (string) get_post_meta($id, QuoteRequest::META_EMAIL, true),
                __('Company', 'quotlet') => (string) get_post_meta($id, QuoteRequest::META_COMPANY, true),
                __('Message', 'quotlet') => (string) get_post_field('post_content', $id),
            ];

            // Add-on answers (a phone number, for instance).
            foreach ($this->requests->answers($id) as $answer) {
                $fields[$answer['label']] = QuoteRequest::answerText($answer);
            }

            $items = get_post_meta($id, QuoteRequest::META_ITEMS, true);
            $names = [];
            foreach (is_array($items) ? $items : [] as $item) {
                if (is_array($item) && isset($item['name'], $item['qty'])) {
                    $names[] = sprintf('%1$s x %2$d', (string) $item['name'], (int) $item['qty']);
                }
            }
            $fields[__('Requested items', 'quotlet')] = implode(', ', $names);

            $rows = [['name' => __('Date', 'quotlet'), 'value' => (string) get_the_date('', $id)]];
            foreach ($fields as $name => $value) {
                if ('' !== $value) {
                    $rows[] = ['name' => (string) $name, 'value' => $value];
                }
            }

            $data[] = [
                'group_id'    => 'quotlet-quote-requests',
                'group_label' => __('Quote requests', 'quotlet'),
                'item_id'     => 'quotlet-quote-' . $id,
                'data'        => $rows,
            ];
        }

        return ['data' => $data, 'done' => count($ids) < self::PER_PAGE];
    }

    /**
     * Anonymise matching requests. The request and its product lines stay
     * for the merchant's records; the contact details, message and add-on
     * answers go. An anonymised request no longer matches, so every call
     * reads the first page.
     *
     * @return array{items_removed: bool, items_retained: bool, messages: list<string>, done: bool}
     */
    public function erase(string $email, int $page = 1): array
    {
        $ids = $this->ids($email, 1);

        foreach ($ids as $id) {
            update_post_meta($id, QuoteRequest::META_NAME, wp_privacy_anonymize_data('text'));
            update_post_meta($id, QuoteRequest::META_EMAIL, wp_privacy_anonymize_data('email'));
            update_post_meta($id, QuoteRequest::META_COMPANY, wp_privacy_anonymize_data('text'));
            delete_post_meta($id, QuoteRequest::META_ANSWERS);
            delete_post_meta($id, QuoteRequest::META_USER_ID);

            wp_update_post([
                'ID'           => $id,
                'post_title'   => sprintf(
                    /* translators: %d: quote request ID */
                    __('Quote request #%d (anonymised)', 'quotlet'),
                    $id,
                ),
                'post_content' => '',
            ]);
        }

        return [
            'items_removed'  => [] !== $ids,
            'items_retained' => false,
            'messages'       => [],
            'done'           => count($ids) < self::PER_PAGE,
        ];
    }

    /**
     * One page of quote request ids sent with $email or by its account.
     *
     * @return list<int>
     */
    private function ids(string $email, int $page): array
    {
        $email = sanitize_email($email);

        if ('' === $email) {
            return [];
        }

        $metaQuery = [
            'relation' => 'OR',
            ['key' => QuoteRequest::META_EMAIL, 'value' => $email],
        ];

        $user = get_user_by('email', $email);

        if ($user instanceof \WP_User) {
            $metaQuery[] = ['key' => QuoteRequest::META_USER_ID, 'value' => (string) $user->ID];
        }

        $ids = get_posts([
            'post_type'        => QuoteRequest::POST_TYPE,
            // Every status, trash included: 'any' would skip trashed requests.
            'post_status'      => array_keys(get_post_stati()),
            'posts_per_page'   => self::PER_PAGE,
            'paged'            => $page,
            'orderby'          => 'ID',
            'order'            => 'ASC',
            'fields'           => 'ids',
            'suppress_filters' => true,
            'meta_query'       => $metaQuery, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- privacy tool, admin only, paged.
        ]);

        return array_map('intval', $ids);
    }
}
