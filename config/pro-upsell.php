<?php
/**
 * PRO upsell content, generated from the plogins.com registry by
 * scripts/gen-pro-upsell.mjs. The admin upsell renders this; curate the
 * feature list to fit this plugin's settings screen (do not invent features).
 *
 * @package plogins-estimate-pro
 */

defined('ABSPATH') || exit;

return [
    'name'       => 'Estimate Pro',
    'url'        => 'https://plogins.com/plogins-estimate-pro/pricing/',
    'sellable'   => true,
    'price_from' => 29,
    'currency'   => 'EUR',
    'price_pln'  => 129,
    'lead'       => [
        'en' => 'CSV export, PDF quote downloads, quote-to-order and customer quote accounts ship in the 0.4.0 release. Custom fields are planned.',
        'pl' => 'Eksport CSV, wyceny PDF, zamiana na zamówienie i konta wycen klientów są dostępne w wydaniu 0.4.0. Własne pola są planowane.',
    ],
    'features'   => [
        [
            'en' => ['title' => 'CSV export', 'desc' => 'Download every stored quote request as a spreadsheet.'],
            'pl' => ['title' => 'Eksport CSV', 'desc' => 'Pobierz wszystkie zapytania o wycenę jako arkusz kalkulacyjny.'],
        ],
        [
            'en' => ['title' => 'PDF quotes', 'desc' => 'Download a PDF for a single quote with customer details and line items.'],
            'pl' => ['title' => 'Wyceny PDF', 'desc' => 'Pobierz PDF dla pojedynczego zapytania z danymi klienta i listą produktów.'],
        ],
        [
            'en' => ['title' => 'Quote-to-order', 'desc' => 'Convert an approved quote into a WooCommerce order in one click (QuoteToOrderActions, shipped).'],
            'pl' => ['title' => 'Zamiana wyceny na zamówienie', 'desc' => 'Przekształć zatwierdzoną wycenę w zamówienie WooCommerce jednym kliknięciem (QuoteToOrderActions, wdrożone).'],
        ],
        [
            'en' => ['title' => 'Customer quote accounts', 'desc' => 'Logged-in shoppers see past requests in My Account, open details and re-order from a quote (MyAccountQuotes, shipped).'],
            'pl' => ['title' => 'Konta wycen klientów', 'desc' => 'Zalogowani kupujący widzą wcześniejsze zapytania w Moim koncie, szczegóły i ponowne zamówienie z wyceny (MyAccountQuotes, wdrożone).'],
        ],
        [
            'en' => ['title' => 'Custom quote fields (planned)', 'desc' => 'Extra fields on the request form and quote record.'],
            'pl' => ['title' => 'Własne pola wyceny (planowane)', 'desc' => 'Dodatkowe pola w formularzu zapytania i rekordzie wyceny.'],
        ],
        [
            'en' => ['title' => 'Requires the free Estimate', 'desc' => 'Estimate Pro is an add-on to the free plugin, it boots only after the free plugin loads.'],
            'pl' => ['title' => 'Wymaga darmowego Estimate', 'desc' => 'Estimate Pro to rozszerzenie darmowej wtyczki, boota dopiero po jej załadowaniu.'],
        ],
    ],
];
