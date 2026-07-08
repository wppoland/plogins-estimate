=== Plogins Estimate - Request a Quote for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, request a quote, quote, b2b, hide price
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Requiere complementos: woocommerce
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Permita que los clientes soliciten un presupuesto en lugar de comprar directamente, ideal para B2B y por encargo.

== Description ==

La estimación convierte los productos WooCommerce en solicitudes de cotización. En cotización habilitada
productos, cambia el botón Añadir al carrito por un botón <strong>Añadir a la cotización</strong> y puede
ocultar el precio también. Los clientes recopilan los productos que desean en una cotización.
enumere y envíe sus datos a través de un breve formulario de solicitud. Cada envío es
enviado por correo electrónico y guardado como un registro privado que puede abrir en wp-admin.

Se adapta a tiendas B2B, mayoristas, pedidos al por mayor y productos hechos a pedido donde
Los precios se negocian en lugar de fijarse.

El complemento aún no está en WordPress.org. El código, las versiones y el rastreador de problemas.
en vivo en GitHub: https://github.com/wppoland/plogins-estimate; informes de errores y extracción
Las solicitudes son bienvenidas allí.

= Documentation and links =

* <strong>Documentación</strong> - https://plogins.com/es/plogins-estimate/docs/
* <strong>Página de complementos</strong> - https://plogins.com/es/plogins-estimate/
* <strong>Código fuente</strong> - https://github.com/wppoland/plogins-estimate
* <strong>Informes de errores y solicitudes de funciones</strong> - https://github.com/wppoland/plogins-estimate/issues


= Features =

* Dos modos de cotización: habilitar cotizaciones para <strong>productos seleccionados</strong> o para <strong>todos los productos</strong>.
* Alternancia por producto en el editor de productos (modo seleccionado).
* Reemplaza el botón Añadir al carrito con un botón <strong>Añadir a la cotización</strong> en las páginas y listados de productos.
* Opcionalmente, oculta el precio de los productos habilitados para cotizar.
* Lista de cotizaciones por visitante almacenada en una cookie, por lo que los compradores desconectados pueden usarla sin una cuenta.
* Un shortcode `[estimate_quote]` que muestra la lista de cotizaciones y un formulario de solicitud (nombre, correo electrónico, empresa, mensaje).
* Edición de cantidades y eliminación por artículo en la página de cotización.
* Al enviar, envía un correo electrónico al destinatario que configuró y guarda la solicitud como un tipo de publicación privada personalizada.
* Correo electrónico del destinatario configurable y texto del botón del escaparate.
* El flujo de añadir a cotización funciona sin JavaScript; el marcado utiliza etiquetas y atributos ARIA y se redistribuye en pantallas pequeñas.
* Se envía con un archivo POT para traducción, además de una traducción al polaco (pl_PL).
* Declara compatibilidad con HPOS y bloques de carrito/pago.
* Al eliminar, elimina sus propias opciones; Las solicitudes de cotización guardadas se conservan para que una reinstalación no las pierda.

= The [estimate_quote] shortcode =

Cree una página (por ejemplo, "Solicitar una cotización") y añade el código corto:

`[estimate_quote]`

La página muestra la lista de cotizaciones actual y el formulario de solicitud. Cuando la lista es
vacío, muestra un mensaje corto con un enlace a la tienda.

== Installation ==

1. Cargue el complemento en `/wp-content/plugins/estimate`, o instálelo a través de Complementos → Añadir nuevo.
2. Actívalo. WooCommerce debe estar activo.
3. Vaya a <strong>WooCommerce → Estimación</strong> y elija el modo de cotización y las opciones.
4. Cree una página con el código abreviado `[estimate_quote]` para alojar la lista de cotizaciones y el formulario de solicitud.
5. En el modo "seleccionado", edite un producto y marque <strong>Habilitar solicitudes de cotización</strong> en el cuadro Datos del producto.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Sí. WooCommerce debe estar instalado y activo.

= Where do quote requests go? =

Cada envío se envía por correo electrónico al destinatario que configuraste (o al correo electrónico del administrador del sitio mediante
predeterminado) y se guarda como un registro privado de "Solicitud de cotización" en WooCommerce
menú en wp-admin.

= Can I enable quotes for only some products? =

Sí. Configure el modo de cotización en "Solo productos seleccionados" y marque <strong>Habilitar solicitudes de cotización</strong> en cada producto que desee. Elija "Todos los productos" para aplicarlo en toda la tienda.

= Does the quote list work for logged-out visitors? =

Sí. La lista se almacena en una cookie por visitante, por lo que no se requiere ninguna cuenta.

= Can I hide prices on quote-enabled products? =

Sí. La estimación puede ocultar los precios de los productos mientras los compradores crean una lista de cotizaciones y envían una solicitud.


= Does this plugin work on WordPress Multisite? =

Sí. Este complemento es compatible con WordPress Multisite. Activarlo en red o activarlo en sitios individuales; Cada sitio mantiene su propia configuración y datos.

== Screenshots ==

1. El botón Añadir a cotización reemplaza el botón Añadir al carrito en un producto.
2. La página de cotización: lista, cantidades y formulario de solicitud.
3. La pantalla de configuración de Estimación en WooCommerce.
4. Una solicitud de cotización guardada en wp-admin.

== External Services ==

Este complemento no se conecta, envía datos ni carga nada desde ningún servicio externo. Todo se ejecuta en tu propio sitio. Las solicitudes de cotización se guardan localmente como publicaciones privadas de `estimate_quote` con los detalles del cliente (nombre, correo electrónico, empresa y artículos elegidos) mantenidos en la meta de publicación `_estimate_*`, la opción de suscripción por producto se encuentra en la meta clave `_estimate_quote_enabled` y las configuraciones se almacenan en la opción `estimate_settings`. Las listas de cotizaciones en progreso de los compradores se guardan en una cookie de origen en tu dominio, no en ningún servidor de terceros. Cuando se envía una cotización, el correo electrónico de notificación se envía a través del propio `wp_mail()` de WordPress al destinatario que configures (el correo electrónico del administrador del sitio de forma predeterminada); ningún otro servicio de entrega está involucrado. El CSS y JavaScript incluidos se sirven desde la carpeta del complemento, sin CDN, fuentes, mapas ni análisis remotos.

== Changelog ==

= 1.0.1 =
* Primera versión estable.

= 0.1.2 =
* Renombrado a Plogins Estimate for WooCommerce para obtener un nombre de complemento más distintivo.

= 0.1.1 =
* Almacene la identificación del usuario que envía las solicitudes de cotización cuando el comprador inicia sesión.
* Añade el filtro `estimate/customer_quotes` y la acción `estimate/quote_created` para cuentas de clientes PRO.

= 0.1.0 =
* Lanzamiento inicial: modos de cotización (seleccionados/todos), botón Añadir a cotización, ocultación de precios, lista de cotizaciones por visitante, página `[estimate_quote]` con formulario de solicitud, correo electrónico del comerciante y un registro privado de solicitud de cotización.
