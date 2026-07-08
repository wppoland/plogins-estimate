=== Plogins Estimate - Request a Quote for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, request a quote, quote, b2b, hide price
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Wymaga wtyczek: woocommerce
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Pozwól klientom poprosić o wycenę zamiast kupować bezpośrednio, idealne rozwiązanie dla B2B i na zamówienie.

== Description ==

Estimate zamienia produkty WooCommerce w prośby o wycenę. Z włączoną możliwością wyceny
produktów, zamienia przycisk „dodaj do koszyka” na przycisk <strong>Dodaj do wyceny</strong> i może
ukryj także cenę. Klienci zbierają produkty, które chcą, w wycenie
listę i wyślij swoje dane za pomocą krótkiego formularza zapytania. Każde zgłoszenie jest
wysłane do Ciebie e-mailem i zapisane jako prywatny rekord, który możesz otworzyć w wp-admin.

Pasuje do sklepów B2B, hurtowni, zamówień hurtowych i produktów na zamówienie, gdzie
ceny są negocjowane, a nie stałe.

Wtyczki nie ma jeszcze na WordPress.org. Kod, wydania i narzędzie do śledzenia problemów
na żywo na GitHubie: https://github.com/wppoland/plogins-estimate; raporty o błędach i pull
prośby są tam mile widziane.

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
* Lista cytatów poszczególnych gości przechowywana w pliku cookie, dzięki czemu niezalogowani klienci mogą z niej korzystać bez konieczności zakładania konta.
* Krótki kod `[estimate_quote]`, który wyświetla listę ofert i formularz zapytania (imię i nazwisko, adres e-mail, firma, wiadomość).
* Edycja ilości i usuwanie poszczególnych pozycji na stronie oferty.
* Po przesłaniu wysyła wiadomość e-mail do ustawionego odbiorcy i zapisuje żądanie jako prywatny, niestandardowy typ postu.
* Konfigurowalny adres e-mail odbiorcy i tekst przycisku w witrynie sklepowej.
* Proces dodawania do wyceny działa bez JavaScript; znacznik wykorzystuje etykiety i atrybuty ARIA oraz przepływy na małych ekranach.
* Dostarczany z plikiem POT do tłumaczenia oraz tłumaczeniem na język polski (pl_PL).
* Deklaruje kompatybilność HPOS i bloków koszyka/kasy.
* Po usunięciu usuwa własne opcje; zapisane prośby o wycenę są przechowywane, aby ponowna instalacja ich nie utraciła.

= The [estimate_quote] shortcode =

Utwórz stronę (np. „Poproś o wycenę”) i dodaj krótki kod:

`[estimate_quote]`

Na stronie wyświetlana jest aktualna lista ofert oraz formularz zapytania. Kiedy jest lista
pusty, zamiast tego wyświetla krótką wiadomość z linkiem do sklepu.

== Installation ==

1. Prześlij wtyczkę do `/wp-content/plugins/estimate` lub zainstaluj poprzez Wtyczki → Dodaj nową.
2. Aktywuj. WooCommerce musi być aktywny.
3. Przejdź do <strong>WooCommerce → Oszacuj</strong> i wybierz tryb wyceny oraz opcje.
4. Utwórz stronę z krótkim kodem `[estimate_quote]`, na której będzie dostępna lista ofert i formularz zapytań.
5. W trybie „wybranym” edytuj produkt i zaznacz <strong>Włącz prośby o wycenę</strong> w polu Dane produktu.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Tak. WooCommerce musi być zainstalowany i aktywny.

= Where do quote requests go? =

Każde zgłoszenie jest wysyłane e-mailem do wybranego przez Ciebie odbiorcy (lub adresu e-mail administratora witryny na adres
domyślnie) i zapisany jako prywatny rekord „Zapytanie o wycenę” w WooCommerce
menu w wp-admin.

= Can I enable quotes for only some products? =

Tak. Ustaw tryb wyceny na „Tylko wybrane produkty” i zaznacz <strong>Włącz prośby o wycenę</strong> przy każdym żądanym produkcie. Wybierz opcję „Wszystkie produkty”, aby zastosować ją w całym sklepie.

= Does the quote list work for logged-out visitors? =

Tak. Lista jest przechowywana w pliku cookie dla każdego odwiedzającego, więc konto nie jest wymagane.

= Can I hide prices on quote-enabled products? =

Tak. Oszacowanie może ukryć ceny produktów, podczas gdy kupujący tworzą listę ofert i przesyłają prośby.


= Does this plugin work on WordPress Multisite? =

Tak. Ta wtyczka jest kompatybilna z WordPress Multisite. Aktywuj go w sieci lub aktywuj na poszczególnych stronach; każda witryna przechowuje własne ustawienia i dane.

== Screenshots ==

1. Przycisk Dodaj do wyceny zastępujący dodanie produktu do koszyka.
2. Strona wyceny: lista, ilości i formularz zapytania.
3. Ekran ustawień szacunkowych w WooCommerce.
4. Zapisane zapytanie ofertowe w wp-admin.

== External Services ==

Ta wtyczka nie łączy się, nie wysyła danych ani nie ładuje niczego z żadnej usługi zewnętrznej. Wszystko działa na Twojej własnej stronie. Prośby o wycenę są zapisywane lokalnie jako prywatne posty „estimate_quote” z danymi klienta (imię i nazwisko, adres e-mail, firma i wybrane pozycje) przechowywanymi w meta postach „_estimate_*”, możliwość zapisania się dla poszczególnych produktów znajduje się w metakluczu „_estimate_quote_enabled”, a ustawienia są przechowywane w opcji „estimate_settings”. Listy ofert kupujących w toku są przechowywane we własnym pliku cookie w Twojej domenie, a nie na serwerze strony trzeciej. Kiedy wycena zostanie przesłana, wiadomość e-mail z powiadomieniem zostanie wysłana za pośrednictwem funkcji `wp_mail()' WordPressa do skonfigurowanego przez Ciebie odbiorcy (domyślnie jest to adres e-mail administratora witryny); żadna inna usługa dostawy nie jest zaangażowana. Dołączone CSS i JavaScript są obsługiwane z folderu wtyczek, bez zdalnego CDN, czcionek, map i analiz.

== Changelog ==

= 1.0.1 =
* Pierwsza stabilna wersja.

= 0.1.2 =
* Zmieniono nazwę na Plogins Estimate dla WooCommerce, aby uzyskać bardziej charakterystyczną nazwę wtyczki.

= 0.1.1 =
* Przechowuj przesyłany identyfikator użytkownika w zapytaniach o wycenę, gdy kupujący jest zalogowany.
* Dodano filtr „wycena/wycena_klienta” i akcję „utworzono wycenę/wycenę” dla kont klientów PRO.

= 0.1.0 =
* Pierwsza wersja: tryby wyceny (wybrane/wszystkie), przycisk Dodaj do wyceny, ukrywanie cen, lista ofert dla poszczególnych gości, strona `[estimate_quote]` z formularzem zapytania, e-mail sprzedawcy i prywatny rekord zapytania o wycenę.
