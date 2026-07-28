=== Plogins Estimate - Request a Quote for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, request a quote, quote, b2b, hide price
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Requires Plugins: woocommerce
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Pozwól klientom poprosić o wycenę zamiast kupować bezpośrednio, idealne rozwiązanie dla B2B i na zamówienie.

== Description ==

Estimate zamienia produkty WooCommerce w zapytania o wycenę. W przypadku produktów z włączoną wyceną
zastępuje przycisk „dodaj do koszyka” przyciskiem <strong>Dodaj do wyceny</strong>, a także może
ukryć cenę. Klienci zbierają wybrane produkty na liście wyceny
i przesyłają swoje dane za pomocą krótkiego formularza zapytania. Każde zgłoszenie jest
wysyłane do Ciebie e-mailem i zapisywane jako prywatny rekord, który możesz otworzyć w wp-admin.

Pasuje do sklepów B2B, hurtowni, zamówień hurtowych i produktów na zamówienie, gdzie
ceny są negocjowane, a nie stałe.

Wtyczki nie ma jeszcze na WordPress.org. Kod, wydania i narzędzie do śledzenia problemów
znajdują się na GitHubie: https://github.com/wppoland/plogins-estimate; raporty o błędach i pull
requesty są tam mile widziane.

= Documentation and links =

* <strong>Dokumentacja</strong> - https://plogins.com/pl/plogins-estimate/docs/
* <strong>Strona wtyczki</strong> - https://plogins.com/pl/plogins-estimate/
* <strong>Kod źródłowy</strong> - https://github.com/wppoland/plogins-estimate
* <strong>Raporty o błędach i prośby o nowe funkcje</strong> - https://github.com/wppoland/plogins-estimate/issues


= Features =

* Dwa tryby wyceny: włącz wycenę dla <strong>wybranych produktów</strong> lub dla <strong>wszystkich produktów</strong>.
* Przełączanie poszczególnych produktów w edytorze produktów (wybrany tryb).
* Zastępuje przycisk Dodaj do koszyka przyciskiem <strong>Dodaj do wyceny</strong> na stronach produktów i listach.
* Opcjonalnie ukrywa cenę produktów objętych wyceną.
* Lista wyceny dla poszczególnego odwiedzającego przechowywana w pliku cookie, dzięki czemu niezalogowani klienci mogą z niej korzystać bez zakładania konta.
* Shortcode `[estimate_quote]`, który wyświetla listę wyceny i formularz zapytania (imię i nazwisko, adres e-mail, firma, wiadomość).
* Edycja ilości i usuwanie poszczególnych pozycji na stronie wyceny.
* Po przesłaniu wysyła wiadomość e-mail do ustawionego odbiorcy i zapisuje zgłoszenie jako prywatny, niestandardowy typ wpisu.
* Konfigurowalny adres e-mail odbiorcy i tekst przycisku w sklepie.
* Proces dodawania do wyceny działa bez JavaScriptu; znaczniki wykorzystują etykiety i atrybuty ARIA oraz dostosowują układ na małych ekranach.
* Dostarczany z plikiem POT do tłumaczenia oraz tłumaczeniem na język polski (pl_PL).
* Deklaruje kompatybilność HPOS i bloków koszyka/kasy.
* Po usunięciu usuwa własne opcje; zapisane prośby o wycenę są przechowywane, aby ponowna instalacja ich nie utraciła.

= The [estimate_quote] shortcode =

Utwórz stronę (np. „Poproś o wycenę”) i dodaj shortcode:

`[estimate_quote]`

Na stronie wyświetlana jest aktualna lista wyceny oraz formularz zapytania. Gdy lista jest
pusta, zamiast niej wyświetlany jest krótki komunikat z linkiem powrotnym do sklepu.

== Installation ==

1. Prześlij wtyczkę do `/wp-content/plugins/estimate` lub zainstaluj poprzez Wtyczki → Dodaj nową.
2. Aktywuj. WooCommerce musi być aktywny.
3. Przejdź do <strong>WooCommerce → Estimate</strong> i wybierz tryb wyceny oraz opcje.
4. Utwórz stronę z shortcodem `[estimate_quote]`, na której znajdą się lista wyceny i formularz zapytania.
5. W trybie „wybranym” edytuj produkt i zaznacz <strong>Włącz prośby o wycenę</strong> w polu Dane produktu.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Tak. WooCommerce musi być zainstalowany i aktywny.

= Where do quote requests go? =

Każde zgłoszenie jest wysyłane e-mailem do wybranego przez Ciebie odbiorcy (lub domyślnie na adres
e-mail administratora witryny) i zapisywane jako prywatny rekord „Zapytanie o wycenę” w menu
WooCommerce w wp-admin.

= Can I enable quotes for only some products? =

Tak. Ustaw tryb wyceny na „Tylko wybrane produkty” i zaznacz <strong>Włącz prośby o wycenę</strong> przy każdym żądanym produkcie. Wybierz opcję „Wszystkie produkty”, aby zastosować ją w całym sklepie.

= Does the quote list work for logged-out visitors? =

Tak. Lista jest przechowywana w pliku cookie dla każdego odwiedzającego, więc konto nie jest wymagane.

= Can I hide prices on quote-enabled products? =

Tak. Estimate może ukrywać ceny produktów, gdy kupujący tworzą listę wyceny i przesyłają zapytanie.


= Does this plugin work on WordPress Multisite? =

Tak. Ta wtyczka jest kompatybilna z WordPress Multisite. Włącz ją dla całej sieci lub na poszczególnych stronach; każda witryna przechowuje własne ustawienia i dane.

== Screenshots ==

1. Przycisk Dodaj do wyceny zastępujący dodanie produktu do koszyka.
2. Strona wyceny: lista, ilości i formularz zapytania.
3. Ekran ustawień Estimate w WooCommerce.
4. Zapisane zapytanie ofertowe w wp-admin.

== External Services ==

Ta wtyczka nie łączy się z żadną usługą zewnętrzną, nie wysyła do niej danych ani niczego z niej nie ładuje. Wszystko działa w Twojej własnej witrynie. Zapytania o wycenę są zapisywane lokalnie jako prywatne wpisy `estimate_quote` z danymi klienta (imię i nazwisko, adres e-mail, firma i wybrane pozycje) przechowywanymi w metadanych wpisu `_estimate_*`, zgoda dla poszczególnych produktów znajduje się w kluczu meta `_estimate_quote_enabled`, a ustawienia są przechowywane w opcji `estimate_settings`. Tworzone listy wyceny kupujących są przechowywane w pliku cookie własnej domeny (first-party), a nie na serwerze podmiotu trzeciego. Gdy wycena zostanie przesłana, e-mail z powiadomieniem jest wysyłany za pomocą funkcji `wp_mail()` WordPressa do skonfigurowanego przez Ciebie odbiorcy (domyślnie na adres e-mail administratora witryny); nie jest zaangażowana żadna inna usługa dostarczania. Dołączone pliki CSS i JavaScript są serwowane z folderu wtyczki, bez zdalnego CDN, czcionek, map czy analityki.

== Translations ==

Plogins Estimate zawiera tłumaczenia interfejsu wtyczki na język polski, niemiecki i hiszpański. Domena tekstowa to `plogins-estimate`, więc pakiety językowe WordPress.org mogą również zastąpić lub rozszerzyć te dołączone tłumaczenia.

== Changelog ==

= 1.0.2 =
* Dodano dołączone tłumaczenia na język polski, niemiecki i hiszpański dla interfejsu wtyczki.

= 1.0.1 =
* Pierwsza stabilna wersja.

= 0.1.2 =
* Zmieniono nazwę na Plogins Estimate for WooCommerce, aby nadać wtyczce bardziej charakterystyczną nazwę.

= 0.1.1 =
* Zapisywanie identyfikatora przesyłającego użytkownika w zapytaniach o wycenę, gdy kupujący jest zalogowany.
* Dodano filtr `estimate/customer_quotes` i akcję `estimate/quote_created` dla kont klientów PRO.

= 0.1.0 =
* Pierwsza wersja: tryby wyceny (wybrane/wszystkie), przycisk Dodaj do wyceny, ukrywanie cen, lista wyceny dla poszczególnych odwiedzających, strona `[estimate_quote]` z formularzem zapytania, e-mail sprzedawcy i prywatny rekord zapytania o wycenę.
