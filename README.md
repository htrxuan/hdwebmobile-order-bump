# HDWebmobile Order Bump

A one-click discounted add-on offer at checkout -- the price charged is always the admin's own configured price, never accepted from the request.

- **WordPress.org:** https://wordpress.org/plugins/hdwebmobile-order-bump/
- **Requires:** WordPress 6.9+, WooCommerce, PHP 7.4+
- **License:** GPLv2 or later

## Description

Shows one extra product at a discount on the cart page. A shopper clicks a single link to add it -- no form, no quantity field, nothing to submit -- and the discounted price is computed by the server at that moment from the product's own regular price and the configured discount percentage.

## Why this plugin exists

Several competing "order bump" and "one-click upsell" plugins have computed the bumped item's discounted price on the checkout page and then read that price back from a hidden form field when the order was placed (CWE-20, Improper Input Validation). A shopper who edited that field, or simply replayed the request with a different value, could add the bump item at almost any price of their choosing.

This plugin has no such field to tamper with in the first place:

* Adding the offer is a plain link, not a form -- there is nothing to submit a price through.
* The discounted price is computed by one method, `get_discounted_price()`, which takes no arguments at all. It always derives the price fresh from the product's own current regular price and the store's saved discount percentage.
* That same method is called again every time cart totals are calculated, so the price is never "locked in" from whenever the item was added -- and if the store owner later points the offer at a different product, a leftover item from the old offer reverts to its own real price rather than keeping a stale discount.

## Features

* One admin-configured bump offer shown on the cart page
* Works on both classic and block-based (Cart block) themes
* The offer only shows while the configured product is genuinely in stock and purchasable

## Limitations

* One offer at a time, shown on the cart page (not per-product upsells, and not a checkout-page field)
* No A/B testing or conversion analytics in this version

## Installation

1. Upload to `/wp-content/plugins/hdwebmobile-order-bump`, or install through the WordPress plugins screen.
2. Activate. WooCommerce must already be installed and active.
3. Go to **WooCommerce > HDWebmobile > Order Bump** to choose a product and discount.

## Development

```
includes/
  class-hdob-activator.php    activation check
  class-hdob-core.php         bootstraps admin + frontend
  class-hdob-repository.php   settings + the ONLY place a bump price is ever computed
  class-hdob-admin.php        hub tab and settings form
  class-hdob-frontend.php     cart-page offer, add-to-cart link, price enforcement
  class-hdob-hub.php          shared HDWebmobile admin hub (see the suite's other plugins)
```

## License

GPLv2 or later — https://www.gnu.org/licenses/gpl-2.0.html
