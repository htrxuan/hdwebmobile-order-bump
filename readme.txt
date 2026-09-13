=== HDWebmobile Order Bump ===
Contributors: htrxuan
Donate link: https://paypal.me/htrxuan/20
Tags: woocommerce, order bump, upsell, cart, cross-sell
Requires at least: 6.9
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
Requires Plugins: woocommerce
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A one-click discounted add-on offer at checkout -- the price charged is always the admin's own configured price, never accepted from the request.

== Description ==

HDWebmobile Order Bump shows one extra product at a discount on the cart page. A shopper clicks a single link to add it -- no form, no quantity field, nothing to submit -- and the discounted price is computed by the server at that moment from the product's own regular price and your configured discount percentage.

= Why this plugin exists =
Several competing "order bump" and "one-click upsell" plugins have computed the bumped item's discounted price on the checkout page and then read that price back from a hidden form field when the order was placed (CWE-20, Improper Input Validation). A shopper who edited that field, or simply replayed the request with a different value, could add the bump item at almost any price of their choosing.

This plugin has no such field to tamper with in the first place:

* Adding the offer is a plain link, not a form -- there is nothing to submit a price through.
* The discounted price is computed by one method, `get_discounted_price()`, which takes no arguments at all. It always derives the price fresh from the product's own current regular price and the store's saved discount percentage.
* That same method is called again every time cart totals are calculated, so the price is never "locked in" from whenever the item was added -- and if the store owner later points the offer at a different product, a leftover item from the old offer reverts to its own real price rather than keeping a stale discount.

= Key Features =
* One admin-configured bump offer shown on the cart page
* Works on both classic and block-based (Cart block) themes
* The offer only shows while the configured product is genuinely in stock and purchasable

= Limitations (please read before installing) =
* One offer at a time, shown on the cart page (not per-product upsells, and not a checkout-page field)
* No A/B testing or conversion analytics in this version

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/hdwebmobile-order-bump` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress. WooCommerce must already be installed and active.
3. Go to **WooCommerce > HDWebmobile > Order Bump** to choose a product and discount.

== How to Use ==

= 1. Configure the offer =
Pick a product, a discount percentage, and a headline.

= 2. Shoppers see it on the cart page =
A single "Add this deal" link adds the product at the discounted price -- computed server-side every time.

== Screenshots ==

1. The order-bump offer on the cart page.
2. The settings screen under WooCommerce > HDWebmobile.

== Changelog ==

= 1.0.0 =
* Initial release: a single cart-page order bump, priced entirely server-side from the product's real regular price.
