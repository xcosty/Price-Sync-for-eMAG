=== Price Sync for eMAG ===
Contributors: xCosty
Tags: woocommerce, emag, price sync, stock sync, marketplace
Requires at least: 4.7
Tested up to: 6.6
Stable tag: 1.5.1
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Sync WooCommerce product prices and stock with eMAG Marketplace, including advanced settings for added functionality.

== Description ==

Stable tag: 1.5
Am corectat partea de sincronizare in baza ID-ului si PNK
Am adaugat in taburile woocommerce posibilitatea adaugarii PNK si ID pentru imperechere produs
Extras URL-ul de accesare direct in meniul principal
Optimizat cod 

Stable tag: 1.4.9
Corectii si aducere la conformitate 

Stable tag: 1.4.5
Corectii validare modul

Stable tag: 1.4.4
Corectii denumire modul

Stable tag: 1.4.2
Corectii cod conform cerinte wordpress

WooCommerce Price Sync for eMAG is a plugin that allows WooCommerce store owners to automatically sync product prices and stock with eMAG Marketplace via API. It includes advanced settings for price markup, selective brand syncing, stock conditions, and more.

**Key Features**:
* Sync prices and stock with eMAG Marketplace automatically
* Apply price multipliers to adjust prices before syncing
* Sync specific brands only
* Define brands that will use real stock and those with a custom stock value
* Associate custom IDs between eMAG and WooCommerce
* Sync scheduled via WordPress cron job every hour (customizable)
* Optional logging for debugging API calls

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/woocommerce-emag-price-sync` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to **Settings > Price Sync for eMAG** to configure the API and advanced options.

== Frequently Asked Questions ==

= Does this plugin work with any WooCommerce store? =

Yes, as long as your WooCommerce store has products and uses eMAG Marketplace, the plugin will sync prices and stock via the eMAG API.

= How often does the plugin sync with eMAG? =

By default, the sync occurs every hour, but this can be customized by modifying the WordPress cron job frequency.

= What brands are synced? =

You can select which brands to sync with eMAG via the settings in the "Brands to Sync" section.

== Changelog ==

= 1.3 =
* Added logging functionality for API calls
* Improved brand selection interface
* Added price multiplier and sale price multiplier settings
* Added option to define stock values for brands with no stock

== License ==

This plugin is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This plugin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this plugin. If not, see [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html).

== Upgrade Notice ==

= 1.3 =
Ensure that you have configured the new price multiplier settings and verified the API credentials to ensure seamless synchronization with eMAG Marketplace.
