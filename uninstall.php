<?php
/**
 * Uninstall cleanup for Estimate.
 *
 * Runs when the plugin is deleted from wp-admin. Removes the plugin's options.
 * Submitted quote requests (the estimate_quote custom post type) and the
 * per-product quote flag are intentionally left in place: they are merchant
 * content that should survive a reinstall and can be removed manually.
 *
 * @package Estimate
 */

declare(strict_types=1);

defined('WP_UNINSTALL_PLUGIN') || exit;

/**
 * Remove this plugin's options from the current site.
 */
function estimate_uninstall_site(): void
{
    delete_option('estimate_settings');
    delete_option('estimate_db_version');
    delete_option('estimate_quote_page_id');
}

// Options are per site, so a network uninstall has to visit every site.
if (is_multisite()) {
    foreach (get_sites(['fields' => 'ids', 'number' => 0]) as $estimate_site_id) {
        switch_to_blog((int) $estimate_site_id);
        estimate_uninstall_site();
        restore_current_blog();
    }
    unset($estimate_site_id);
} else {
    estimate_uninstall_site();
}

// The PRO banner's dismissal is stored per user, so it belongs to the
// plugin rather than to the site content. User meta is global, not
// per-site, which is why this uses delete_metadata's \$delete_all rather
// than a loop over the users of one blog.
delete_metadata('user', 0, 'estimate_pro_banner_dismissed', '', true);
