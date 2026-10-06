=== Quotlet - Request a Quote for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, request a quote, quote, b2b, hide price
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.1
Requires Plugins: woocommerce
Stable tag: 1.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Let customers request a quote instead of buying directly, ideal for B2B and made-to-order.

== Description ==

Quotlet turns WooCommerce products into quote requests. On quote-enabled
products it swaps the add-to-cart button for an **Add to quote** button, and can
hide the price as well. Customers collect the products they want into a quote
list and send their details through a short request form. Each submission is
emailed to you and saved as a private record you can open in wp-admin.

It suits B2B stores, wholesale, bulk orders and made-to-order products where
prices are negotiated rather than fixed.

The code, releases and issue tracker live on GitHub: [github.com/wppoland/plogins-estimate](https://github.com/wppoland/plogins-estimate); bug reports and pull
requests are welcome there.

= Documentation and links =

* **Documentation**: [plogins.com/plogins-estimate/docs/](https://plogins.com/plogins-estimate/docs/)
* **Plugin page**: [plogins.com/plogins-estimate/](https://plogins.com/plogins-estimate/)
* **Source code**: [github.com/wppoland/plogins-estimate](https://github.com/wppoland/plogins-estimate)
* **Bug reports and feature requests**: [github.com/wppoland/plogins-estimate/issues](https://github.com/wppoland/plogins-estimate/issues)


= Features =

* Two quote modes: enable quotes for **selected products** or for **all products**.
* Per-product toggle in the product editor (selected mode).
* Replaces the add-to-cart button with an **Add to quote** button on product pages and listings.
* Optionally hides the price on quote-enabled products.
* Per-visitor quote list stored in a cookie, so logged-out shoppers can use it without an account.
* A `[estimate_quote]` shortcode that shows the quote list and a request form (name, email, company, message).
* Quantity editing and per-item removal on the quote page.
* On submit, emails the recipient you set and saves the request as a private custom post type.
* Configurable recipient email and storefront button text.
* The add-to-quote flow works without JavaScript; the markup uses labels and ARIA attributes and reflows on small screens.
* Ships with a POT file for translation; translations come from translate.wordpress.org language packs.
* Declares HPOS and cart/checkout blocks compatibility.
* On delete, removes its own options; saved quote requests are kept so a reinstall doesn't lose them.

= The [estimate_quote] shortcode =

Create a page (e.g. "Request a Quote") and add the shortcode:

`[estimate_quote]`

The page shows the current quote list and the request form. When the list is
empty it shows a short message with a link back to the shop instead.

== Installation ==

1. Upload the plugin to `/wp-content/plugins/quotlet`, or install via Plugins > Add New.
2. Activate it. WooCommerce must be active.
3. Go to **WooCommerce > Request a Quote** and choose your quote mode and options.
4. Create a page with the `[estimate_quote]` shortcode to host the quote list and request form.
5. In "selected" mode, edit a product and tick **Enable quote requests** in the Product data box.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Yes. WooCommerce must be installed and active.

= Where do quote requests go? =

Each submission is emailed to the recipient you set (or the site admin email by
default) and saved as a private "Quote Request" record under the WooCommerce
menu in wp-admin.

= Can I enable quotes for only some products? =

Yes. Set the quote mode to "Selected products only" and tick **Enable quote
requests** on each product you want. Choose "All products" to apply it store-wide.

= Does the quote list work for logged-out visitors? =

Yes. The list is stored in a cookie per visitor, so no account is required.

= Can I hide prices on quote-enabled products? =

Yes. Quotlet can hide product prices while shoppers build a quote list and submit a request.


= Does this plugin work on WordPress Multisite? =

Yes. This plugin is compatible with WordPress Multisite. Network activate it or activate it on individual sites; each site keeps its own settings and data.

== Screenshots ==

1. The Add to quote button replacing add-to-cart on a product.
2. The quote page: list, quantities and the request form.
3. The Quotlet settings screen under WooCommerce.
4. Stored quote requests in wp-admin.

== External Services ==

This plugin does not connect to, send data to, or load anything from any external service. Everything runs on your own site. Quote requests are saved locally as private `estimate_quote` posts with the customer's details (name, email, company and chosen items) kept in `_estimate_*` post meta, the per-product opt-in lives in the `_estimate_quote_enabled` meta key, and settings are stored in the `estimate_settings` option. Shoppers' in-progress quote lists are held in a first-party cookie on your domain, not on any third-party server. When a quote is submitted, the notification email is sent through WordPress's own `wp_mail()` to the recipient you configure (the site admin email by default); no other delivery service is involved. The bundled CSS and JavaScript are served from the plugin folder, with no remote CDN, fonts, maps or analytics.

== Translations ==

Quotlet is fully translatable and ships the `quotlet.pot` template. Translations are delivered by WordPress.org language packs from translate.wordpress.org, which is where Polish, German and Spanish are being contributed; the package itself carries no compiled translation files.

== Changelog ==

= 1.2.1 =
* The Elementor widget has its own translatable title. It reused the settings menu label, so in Polish the widget was called "Ustawienia zapytań ofertowych" (quote request settings).

= 1.2.0 =
* Elementor widget "Request a Quote": place the quote list and request form with the Elementor editor. It renders the same [estimate_quote] output and only loads when Elementor is active.
* Add to quote also finds a quote page built with that widget. Elementor keeps the widget out of the page content, so such a page was never found and the button landed on the shop page.

= 1.1.9 =
* Add to quote now lands on the page that holds the [estimate_quote] shortcode. The page was never recorded, so every click ended on the shop page with no sign the product had been added.
* Block themes: the add-to-cart form block on a quote product is replaced by the Add to quote button. The native Add to cart button used to stay, with the quote link printed as plain text next to the price.
* Quote products can no longer be bought directly. A link such as ?add-to-cart= or a leftover cart item put them in the cart with their hidden price; WooCommerce now refuses them and drops them from an existing cart.
* Readme: removed the false claims of a bundled Polish translation and that the plugin is not on WordPress.org. The PRO card no longer lists custom quote fields, which are not built.

= 1.1.8 =
* Security (low): quote requests are now shop manager data only. Any Editor could list, open, edit and delete every customer's quote request, with their name, email and company, by opening the quote screens directly; every quote request permission now requires the manage_woocommerce capability.

= 1.1.7 =
* Screenshots retaken under the Quotlet name; the fourth shows the list of stored quote requests.

= 1.1.6 =
* The quote list only keeps published, non password protected, quote-enabled products. A hand-edited list cookie could show the names of draft, private or password protected products on the quote page and in the request email.
* The quote list refuses variation ids: the add link only sends products, and a variation id could reveal the name of a hidden parent product.

= 1.1.5 =
* The upgrade notice's "Coming soon" and "Get notified" labels are English source strings for every language; Polish sites used to get their own Polish source text, which translators in other languages then saw untranslated.

= 1.1.4 =
* The quote form's extra fields are now read from the request in the same step that checks the form's security token, and the quantity inputs are sanitised as they are read. A quote list update with an expired token now shows an error instead of doing nothing.

= 1.1.3 =
* Renamed to Quotlet, a plain English name in place of the Esperanto one. The text domain and the plugin folder follow the name; the stored settings, options and every hook are unchanged.

= 1.1.2 =
* The readme still called the plugin Estimate in two places, including a screenshot caption, after the rename to Quotlet. The screenshot of the settings screen was recaptured so its heading matches the plugin name.

= 1.1.1 =
* Belt-and-braces capability check on the product-level quote setting. WooCommerce already checks it before firing the save, but the guard now sits in the file that does the writing.

= 1.1.0 =
* Renamed to Takso. The WordPress.org review team asks a plugin name to lead with a distinctive, coined identifier rather than a generic descriptive word. Takso is Esperanto for an assessment. The text domain follows the name; the stored data, the settings and every hook are unchanged.

= 1.0.14 =
* Fixed: the PRO upgrade promo kept selling to people who had already bought the paid edition. Only the banner could be dismissed, so the sidebar promo and the locked feature cards followed a paying customer around for good. The promo now checks whether the paid edition is active and steps aside when it is.
* Fixed: arrow glyphs in the admin menu paths, and in the strings handed to translators. An arrow inside a translatable string makes the glyph every translator's problem and changes the layout in any locale that drops it.

= 1.0.13 =
* Fixed: deleting the plugin left the per-user "dismiss" flag from the PRO notice in the database. Uninstall now removes it for every user, not just the one who dismissed it.

= 1.0.12 =
* The translation template was regenerated. It still named an older version of the plugin and pointed at source lines that had since moved, which is what translation tools read to show a string in context.

= 1.0.11 =
* Renamed to Plogins Estimate - Request a Quote for WooCommerce so the name leads with the brand rather than a generic word, which is what the WordPress.org plugin review team asks for. The plugin slug is unchanged.

= 1.0.10 =
* Added: the `estimate/quote_form_fields` filter, so an add-on can append its own qualification questions (text, textarea, select, checkbox) to the quote request form. Answers are validated server-side, saved with the request, shown in the merchant notification email and in the quote's wp-admin detail screen, and readable by key through `QuoteRequest::answers()` for anything downstream that needs them, such as a CRM webhook payload.
* Note: this closes a gap where a paid add-on (Plogins Estimate Pro) shipped and sold this exact feature against a version of this filter that only ever existed on an unreleased development branch. Every install that updates to 1.0.10 or later now has the real thing.

= 1.0.9 =
* Tested against WordPress 7.1. Verified by activating this build on a clean 7.1 install with WooCommerce 11.1, not by editing the header.

= 1.0.8 =
* Fixed the PRO promo on the settings screen quoting a price in PLN. PRO is priced and charged in EUR, so an admin on a Polish site was shown a zloty amount and then billed in euro, and the zloty figure was a fixed conversion that drifted from the real charge as the rate moved. The promo now shows the euro price that is actually taken.

= 1.0.7 =
* Translations: restored the Estimate brand name in the German catalogue and reworded the sentences it had broken.
* Translations: made the Spanish catalogue consistently informal (tú) and switched "cotización" to "presupuesto" to match the rest of the family.

= 1.0.4 =
* Translations: completed Polish, German and Spanish for the PRO upgrade panel.

= 1.0.3 =
* Accessibility improvements to the admin and storefront markup.
* Fixed low-contrast admin headings under an OS dark-mode preference.

= 1.0.2 =
* Added bundled Polish, German and Spanish translations for the plugin interface.

= 1.0.1 =
* First stable release.
= 0.1.2 =
* Renamed to Plogins Estimate for WooCommerce for a more distinctive plugin name.

= 0.1.1 =
* Store the submitting user ID on quote requests when the shopper is logged in.
* Add `estimate/customer_quotes` filter and `estimate/quote_created` action for PRO customer accounts.

= 0.1.0 =
* Initial release: quote modes (selected/all), Add to quote button, price hiding, per-visitor quote list, `[estimate_quote]` page with request form, merchant email and a private quote-request record.

== Upgrade Notice ==

= 1.1.8 =
Security release. Users with the Editor role could open customers' quote requests, including their contact details, by direct link. Only shop managers and administrators can now. Update, nothing else to do.
