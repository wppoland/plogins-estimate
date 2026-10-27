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
    'name'       => 'Quotlet Pro',
    'url'        => 'https://plogins.com/plogins-estimate-pro/pricing/',
    'campaign'   => 'plogins-estimate',
    'locales'    => ['pl', 'de', 'es'],
    'sellable'   => true,
    'price_from' => 29,
    'currency'   => 'EUR',
    'lead'       => [
        'en' => 'Everything below ships in version 1.1.0.',
        'pl' => 'Wszystko poniżej jest dostępne w wersji 1.1.0.',
    ],
    'features'   => [
        [
            'en' => ['title' => 'Ask about this product', 'desc' => 'An Ask a question about this product button on the product page opens a dialog where the shopper asks about that product without the quote list. The enquiry is stored as a quote request and emailed to the Quotlet recipient. Limited to 5 enquiries per hour per IP address.'],
            'pl' => ['title' => 'Zapytanie o produkt', 'desc' => 'Przycisk Zadaj pytanie o ten produkt na stronie produktu otwiera okno, w którym kupujący pyta o ten produkt bez listy zapytań. Zapytanie zapisuje się jako zapytanie ofertowe i trafia mailem do odbiorcy z Quotlet. Limit 5 zapytań na godzinę z jednego adresu IP.'],
        ],
        [
            'en' => ['title' => 'File attachments on enquiries', 'desc' => 'The shopper attaches a PDF, JPG, PNG, WebP, Word, Excel or ZIP file up to 10 MB. It is stored under a random folder name, never linked publicly and deleted with the quote.'],
            'pl' => ['title' => 'Załączniki do zapytań', 'desc' => 'Kupujący dołącza plik PDF, JPG, PNG, WebP, Word, Excel lub ZIP do 10 MB. Plik leży w katalogu o losowej nazwie, nie jest publicznie linkowany i znika razem z zapytaniem.'],
        ],
        [
            'en' => ['title' => 'Counter-offers with Accept and pay', 'desc' => 'On the quote screen enter a negotiated price, an expiry date and a note, then Save and send offer. Accept and pay takes the customer to the payment page of an order at the offered price.'],
            'pl' => ['title' => 'Kontroferty z akceptacją i płatnością', 'desc' => 'Na ekranie zapytania wpisz wynegocjowaną cenę, datę ważności i wiadomość, potem Zapisz i wyślij ofertę. Klient po kliknięciu Zaakceptuj i zapłać trafia na stronę płatności zamówienia w oferowanej cenie.'],
        ],
        [
            'en' => ['title' => 'Quote on out of stock', 'desc' => 'Out-of-stock products switch to quote mode, so the shopper asks instead of leaving. A variable product switches when none of its variations is in stock.'],
            'pl' => ['title' => 'Zapytanie przy braku w magazynie', 'desc' => 'Produkty niedostępne w magazynie przechodzą w tryb zapytań, więc klient pyta zamiast wychodzić. Produkt zmienny przechodzi, gdy żaden wariant nie jest dostępny.'],
        ],
        [
            'en' => ['title' => 'Quote mode by role and category', 'desc' => 'Switch quote mode on for chosen roles, for example wholesale customers or guests who are not logged in, and for chosen categories including their subcategories.'],
            'pl' => ['title' => 'Tryb zapytań według roli i kategorii', 'desc' => 'Włącz tryb zapytań dla wybranych ról, na przykład hurtowników albo niezalogowanych gości, i dla wybranych kategorii razem z podkategoriami.'],
        ],
        [
            'en' => ['title' => 'CSV export', 'desc' => 'Download every stored quote request as a UTF-8 CSV for your spreadsheet.'],
            'pl' => ['title' => 'Eksport CSV', 'desc' => 'Pobierz wszystkie zapytania o wycenę jako plik CSV UTF-8 do arkusza kalkulacyjnego.'],
        ],
        [
            'en' => ['title' => 'PDF quotes', 'desc' => 'Download a PDF for a single request with customer details, requested products and the message.'],
            'pl' => ['title' => 'Wyceny PDF', 'desc' => 'Pobierz PDF dla pojedynczego zapytania z danymi klienta, listą produktów i wiadomością.'],
        ],
        [
            'en' => ['title' => 'Quote-to-order', 'desc' => 'Create a pending WooCommerce order from a quote. An order from a logged-in customer\'s quote belongs to that customer.'],
            'pl' => ['title' => 'Zamiana wyceny na zamówienie', 'desc' => 'Utwórz z zapytania oczekujące zamówienie WooCommerce. Zamówienie z zapytania zalogowanego klienta należy do tego klienta.'],
        ],
        [
            'en' => ['title' => 'Customer quote accounts', 'desc' => 'Logged-in shoppers see past requests in My Account, with details and linked orders, and can load the items into a new quote.'],
            'pl' => ['title' => 'Konta wycen klientów', 'desc' => 'Zalogowani kupujący widzą wcześniejsze zapytania w Moim koncie, ich szczegóły i powiązane zamówienia, i mogą załadować pozycje do nowej wyceny.'],
        ],
        [
            'en' => ['title' => 'CRM webhooks', 'desc' => 'Every new request is POSTed as JSON to your endpoint, with optional HMAC SHA256 signing, one retry and a test button.'],
            'pl' => ['title' => 'Webhooki CRM', 'desc' => 'Każde nowe zapytanie trafia jako JSON pod wskazany adres, z opcjonalnym podpisem HMAC SHA256, jedną ponowną próbą i przyciskiem testu.'],
        ],
        [
            'en' => ['title' => 'Qualification questions', 'desc' => 'Ready-made question sets for e-commerce and retail, installation and field services, and B2B services, added to the request form.'],
            'pl' => ['title' => 'Pytania kwalifikujące', 'desc' => 'Gotowe zestawy pytań dla e-commerce i handlu, montażu i usług w terenie oraz usług B2B dodawane do formularza zapytania.'],
        ],
    ],
];
