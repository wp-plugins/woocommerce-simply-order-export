=== WooCommerce Simply Order Export ===
Contributors: ankitgadertcampcom
Donate link: http://sharethingz.com
Tags: woocommerce, order, export, csv, duration, woocommerce-order, woocommerce-order-export
Requires at least: WordPress 3.9 WooCommerce 2.2.10
Tested up to: WordPress 4.1 + WooCommerce 2.2.10
Stable tag: 1.1.2
License: GPLv2 or later (of-course)
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Export WooCommerce order details in csv format.

== Description ==
This plugin exports data of WooCommerce orders, which may include Customer Name, Product details along with quantity, name and variation details, Amount paid., Customer Email and Order status. The data will be exported in the form of csv file.

You can add more fields to export and extend the functionality by using hooks. It has extensive options in woocommerce setting page, you can select which fields will be present in csv file.

= Features =

* Easy to install and setup
* Very simplified and clean UI.
* Easily customizable
* Exports csv file containing information of WooCommerce orders
* Exports orders between certain duration, you can select start and end date.
* Exports item quantity, product name along with variation details.
* Options for fields which you want to export
* Strong support.
* For customization according to your need contact: https://www.facebook.com/shrthngz
* Very lighweight code.
* Translation ready code.
* Contribution in translating plugin to different languages is strongly encouraged.

== Installation ==
Install WooCommerce Simply Order Export from the 'Plugins' section in your dashboard (Plugins > Add New > Search for WooCommerce Simply Order Export ).

Place the downloaded plugin directory into your wordpress plugin directory.

Activate it through the 'Plugins' section.

== Screenshots ==

1. WooCommerce Simply Order Export setting page.
2. Advanced options.

== Frequently Asked Questions ==

= Export button not working, any problem ? =

Please check your WooCommerce version it should be at least 2.2.10 and WordPress version should be at least 3.9

= How to export orders with specific statuses ? =

Go to advanced options and then check statuses you want to export.

= How to add more fields to csv ? =

Please use wpg_order_columns and wpg_before_csv_write hooks for performing this activity. Little WordPress programming knowledge is necessary for accomplishing this.

== Changelog ==

= 1.0.0 =
* First release.

= 1.0.1 =
* Added 'wpg_order_statuses' hook to filter post status in export query.

= 1.0.2 =
* Added nonce to export request to make it more secure.
* Renamed file order_export_process to order-export-process.
* Check for ABSPATH constant at start of files to avoid data leaks.
* Check for WooCommerce installation in main plugin file.

= 1.0.3 =
* Fixed "invalid request" js error.

= 1.1.0 =
* Fixed Phone number export bug.
* Made plugin translation ready.
* Main file changed to class based structure.
* Added po file for translation contribution.

= 1.1.1 =
* Added advanced options in settings page.
* UI improvements.

= 1.1.2 =
* Fixed script enqueue bug.