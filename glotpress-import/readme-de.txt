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

Lass Kunden ein Angebot anfordern, anstatt direkt zu kaufen, ideal für B2B und Auftragsfertigung.

== Description ==

Estimate verwandelt WooCommerce-Produkte in Angebotsanfragen. Bei angebotsfähigen Produkten
ersetzt es die „In den Warenkorb“-Schaltfläche durch eine <strong>Zum Angebot hinzufügen</strong>-Schaltfläche und
kann zusätzlich den Preis ausblenden. Kunden sammeln die gewünschten Produkte in einer Angebotsliste
und senden ihre Daten über ein kurzes Anfrageformular. Jede Einreichung wird dir
per E-Mail zugesandt und als privater Datensatz gespeichert, den du im WP-Adminbereich öffnen kannst.

Es eignet sich für B2B-Shops, den Großhandel, Großbestellungen und Auftragsfertigung, bei denen
Preise ausgehandelt und nicht fest vorgegeben werden.

Das Plugin ist noch nicht auf WordPress.org verfügbar. Der Code, die Releases und der Issue-Tracker
befinden sich auf GitHub: https://github.com/wppoland/plogins-estimate; Fehlerberichte und Pull
Requests sind dort willkommen.

= Documentation and links =

* <strong>Dokumentation</strong> - https://plogins.com/de/plogins-estimate/docs/
* <strong>Plugin-Seite</strong> - https://plogins.com/de/plogins-estimate/
* <strong>Quellcode</strong> – https://github.com/wppoland/plogins-estimate
* <strong>Fehlerberichte und Funktionsanfragen</strong> – https://github.com/wppoland/plogins-estimate/issues


= Features =

* Zwei Angebotsmodi: Aktiviere Angebote für <strong>ausgewählte Produkte</strong> oder für <strong>alle Produkte</strong>.
* Produktspezifisches Umschalten im Produkteditor (ausgewählter Modus).
* Ersetzt die Schaltfläche „In den Warenkorb“ durch eine Schaltfläche <strong>Zum Angebot hinzufügen</strong> auf Produktseiten und in Auflistungen.
* Optional wird der Preis für angebotsfähige Produkte ausgeblendet.
* Angebotsliste pro Besucher wird in einem Cookie gespeichert, sodass abgemeldete Käufer sie ohne Konto verwenden können.
* Ein `[estimate_quote]`-Shortcode, der die Angebotsliste und ein Anfrageformular (Name, E-Mail, Firma, Nachricht) anzeigt.
* Mengenbearbeitung und Entfernung pro Artikel auf der Angebotsseite.
* Beim Senden wird eine E-Mail an den von dir festgelegten Empfänger gesendet und die Anfrage als privater benutzerdefinierter Beitragstyp gespeichert.
* Konfigurierbare Empfänger-E-Mail und Shop-Schaltflächentext.
* Der Ablauf zum Hinzufügen zum Angebot funktioniert ohne JavaScript; das Markup verwendet Beschriftungen und ARIA-Attribute und passt sich auf kleinen Bildschirmen an.
* Wird mit einer POT-Datei zur Übersetzung sowie einer polnischen (pl_PL) Übersetzung geliefert.
* Erklärt die Kompatibilität von HPOS und Warenkorb-/Checkout-Blöcken.
* Beim Löschen werden die eigenen Optionen entfernt. Gespeicherte Angebotsanfragen bleiben erhalten, sodass sie bei einer Neuinstallation nicht verloren gehen.

= The [estimate_quote] shortcode =

Erstelle eine Seite (z. B. „Angebot anfordern“) und füge den Shortcode hinzu:

`[estimate_quote]`

Die Seite zeigt die aktuelle Angebotsliste und das Anfrageformular. Wenn die Liste
leer ist, wird stattdessen eine kurze Nachricht mit einem Link zurück zum Shop angezeigt.

== Installation ==

1. Lade das Plugin nach `/wp-content/plugins/estimate` hoch oder installiere es über Plugins → Neu hinzufügen.
2. Aktiviere es. WooCommerce muss aktiv sein.
3. Gehe zu <strong>WooCommerce → Estimate</strong> und wähle deinen Angebotsmodus und deine Optionen aus.
4. Erstelle eine Seite mit dem Shortcode `[estimate_quote]`, um die Angebotsliste und das Anfrageformular bereitzustellen.
5. Bearbeite im Modus „Ausgewählt“ ein Produkt und aktiviere im Feld „Produktdaten“ das Kontrollkästchen <strong>Angebotsanfragen aktivieren</strong>.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Ja. WooCommerce muss installiert und aktiv sein.

= Where do quote requests go? =

Jede Übermittlung wird per E-Mail an den von dir festgelegten Empfänger (oder standardmäßig an die
Admin-E-Mail-Adresse der Website) gesendet und als privater „Quote Request“-Datensatz im
WooCommerce-Menü im WP-Adminbereich gespeichert.

= Can I enable quotes for only some products? =

Ja. Stelle den Angebotsmodus auf „Nur ausgewählte Produkte“ ein und aktiviere für jedes gewünschte Produkt das Kontrollkästchen <strong>Angebotsanfragen aktivieren</strong>. Wähle „Alle Produkte“, um es im gesamten Geschäft anzuwenden.

= Does the quote list work for logged-out visitors? =

Ja. Die Liste wird pro Besucher in einem Cookie gespeichert, sodass kein Konto erforderlich ist.

= Can I hide prices on quote-enabled products? =

Ja. Estimate kann Produktpreise ausblenden, während Käufer eine Angebotsliste erstellen und eine Anfrage einreichen.


= Does this plugin work on WordPress Multisite? =

Ja. Dieses Plugin ist mit WordPress Multisite kompatibel. Aktiviere es im Netzwerk oder auf einzelnen Websites. Jede Site behält ihre eigenen Einstellungen und Daten.

== Screenshots ==

1. Die Schaltfläche „Zum Angebot hinzufügen“ ersetzt die Funktion „Zum Warenkorb hinzufügen“ für ein Produkt.
2. Die Angebotsseite: Liste, Mengen und das Anfrageformular.
3. Der Bildschirm mit den Estimate-Einstellungen unter WooCommerce.
4. Eine gespeicherte Angebotsanfrage in wp-admin.

== External Services ==

Dieses Plugin stellt keine Verbindung zu einem externen Dienst her, sendet keine Daten an einen solchen und lädt auch nichts von einem solchen. Alles läuft auf deiner eigenen Website. Angebotsanfragen werden lokal als private `estimate_quote`-Beiträge gespeichert, wobei die Kundendaten (Name, E-Mail-Adresse, Firma und ausgewählte Artikel) im Post-Meta `_estimate_*` gespeichert werden, das Opt-in pro Produkt im Meta-Schlüssel `_estimate_quote_enabled` liegt und die Einstellungen in der Option `estimate_settings` gespeichert werden. Die in Bearbeitung befindlichen Angebotslisten der Käufer werden in einem First-Party-Cookie auf deiner Domain gespeichert, nicht auf einem Drittanbieter-Server. Wenn ein Angebot übermittelt wird, wird die Benachrichtigungs-E-Mail über WordPress' eigenes `wp_mail()` an den von dir konfigurierten Empfänger gesendet (standardmäßig die Admin-E-Mail-Adresse der Website); es ist kein anderer Lieferdienst beteiligt. Das gebündelte CSS und JavaScript werden aus dem Plugin-Ordner bereitgestellt, ohne Remote-CDN, Schriftarten, Karten oder Analysen.

== Translations ==

Plogins Estimate umfasst polnische, deutsche und spanische Übersetzungen für die Plugin-Schnittstelle. Die Textdomain ist `plogins-estimate`, sodass WordPress.org-Sprachpakete diese gebündelten Übersetzungen auch überschreiben oder erweitern können.

== Changelog ==

= 1.0.2 =
* Gebündelte polnische, deutsche und spanische Übersetzungen für die Plugin-Schnittstelle hinzugefügt.

= 1.0.1 =
* Erste stabile Version.

= 0.1.2 =
* Für einen unverwechselbareren Plugin-Namen in Plogins Estimate for WooCommerce umbenannt.

= 0.1.1 =
* Speichere die einreichende Benutzer-ID bei Angebotsanfragen, wenn der Käufer angemeldet ist.
* Füge den Filter `estimate/customer_quotes` und die Aktion `estimate/quote_created` für PRO-Kundenkonten hinzu.

= 0.1.0 =
* Erstveröffentlichung: Angebotsmodi (ausgewählt/alle), Schaltfläche „Zum Angebot hinzufügen“, Preisausblendung, Angebotsliste pro Besucher, Seite `[estimate_quote]` mit Anfrageformular, Händler-E-Mail und ein privater Datensatz für Angebotsanfragen.
