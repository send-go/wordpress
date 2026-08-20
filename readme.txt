=== Sendgo ===
Contributors: amuz
Tags: kakao, alimtalk, sms, woocommerce, notification
Requires at least: 6.0
Tested up to: 7.0
Stable tag: 1.2.4
Requires PHP: 8.2
License: MIT
License URI: https://opensource.org/licenses/MIT

Send Kakao Alimtalk, Kakao Brand Message and SMS/LMS/MMS through Sendgo, including automatic WooCommerce order notifications.

== Description ==

Sendgo (https://sendgo.io) is a Korean business messaging service owned and operated by amuz,
the author of this plugin. This is the official Sendgo plugin, and it connects your WordPress or
WooCommerce site to the Sendgo API.

Korean businesses commonly notify customers over KakaoTalk rather than email. This plugin
lets WordPress send those messages, and lets WooCommerce send them automatically when an
order changes status.

Features:

* Send Kakao Alimtalk and Kakao Brand Message.
* Send SMS, LMS and MMS.
* Automatically notify the buyer when a WooCommerce order moves to "processing" or "completed".
* Fall back to SMS when an Alimtalk message cannot be delivered.
* Simple admin screen built on the WordPress Settings API.

Kakao Friendtalk ("chingutalk") was discontinued on 2025-12-31. Friendtalk requests are now
automatically delivered as Brand Message by Kakao, so existing configurations keep working,
but new integrations should use Brand Message.

The plugin runs server-side only. Your credentials are stored in the WordPress options table
and are never exposed to the front end.

A Sendgo account is required to use this plugin. Message delivery is a paid service; see the
Sendgo site for pricing and terms.

== External services ==

This plugin connects to the Sendgo messaging API (https://sendgo.io) to deliver Kakao
Alimtalk, Kakao Brand Message and SMS/LMS/MMS messages. This is the service the plugin
integrates with; sending messages is not possible without it.

Data is sent to the Sendgo API in the following cases:

* Before any message is sent, an authentication request containing your Access Key and Secret
  Key is sent to obtain a short-lived API token.
* When a WooCommerce order changes to "processing" or "completed", and you have configured a
  template code or SMS fallback text for that status: the customer's billing phone number and
  the order number are sent, along with your sender key and the template code or SMS body.
* When you send a message yourself through the plugin's client: whatever recipient numbers and
  message content you pass to it.

No data is sent when the plugin is merely installed or activated, or when no template code and
no SMS fallback text has been configured.

Service provider: Sendgo — https://sendgo.io
Terms of service: https://sendgo.io/terms-of-service
Privacy policy: https://sendgo.io/privacy-policy

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/sendgo` directory, or install the plugin
   through the WordPress plugins screen.
2. If you installed from source with Composer, run `composer install` in the plugin directory to
   generate `vendor/autoload.php`. The distributed zip already includes it.
3. Activate the plugin through the "Plugins" screen in WordPress.
4. Go to Settings > Sendgo and enter your Access Key, Secret Key, sender keys and API version.
5. If you use WooCommerce, configure the Alimtalk template code and SMS fallback text for each
   order status you want to notify on.

== Frequently Asked Questions ==

= Can I use this without WooCommerce? =

Yes. The Alimtalk and SMS client works without WooCommerce. Only the automatic order
notification feature requires WooCommerce to be active.

= Where do I get my credentials? =

From the Sendgo console at https://sendgo.io.

= Why did my customer not receive a notification? =

Notifications are only sent for order statuses that have a template code or SMS fallback text
configured, and only when the order has a billing phone number. Delivery failures are written
to the WooCommerce log under the "sendgo" source and never interrupt the order flow.

= Will the same notification be sent twice? =

No. Each order status is recorded on the order once its notification succeeds, so re-saving an
order or a status change triggered by another plugin will not send a duplicate.

== Changelog ==

= 1.2.4 =
* Translated the readme, the plugin header description and all translatable strings into
  English so the plugin can be translated on translate.wordpress.org.
* Documented the external service the plugin connects to and the data sent to it.
* Corrected the "Contributors" list.
* Fixed the `url` option being discarded whenever the settings form was saved.

= 1.2.3 =
* Fixed an upload rejection caused by the plugin header having an identical Plugin URI and
  Author URI. Plugin URI now points at the repository, Author URI at the service homepage.

= 1.2.2 =
* Prepared for wordpress.org distribution. The core SDK (sendgo/php) is bundled in vendor/ so
  the plugin installs and runs without Composer.
* Fixed a version mismatch between the plugin header (1.2.1) and the SENDGO_VERSION constant
  (1.1.0).
* Fixed the plugin silently doing nothing when the core SDK could not be found. Installation
  and configuration looked fine while no notification was ever sent; the cause is now shown
  as an admin notice.
* Updated "Tested up to" to WordPress 7.0 and filled in the changelog.

= 1.2.1 =
* Replaced Friendtalk with Brand Message in the plugin description shown in the directory.

= 1.2.0 =
* Reflected the Friendtalk discontinuation (2025-12-31). From 2026-01-01, Friendtalk requests
  are automatically delivered as Brand Message (free-form) by Kakao. Added migration notes and
  a message type mapping table (FT to BT, and so on) to the documentation.
* Updated the bundled core SDK to sendgo/php 1.2.1.

= 1.1.0 =
* Added short URL API support (via the core SDK).
* Split templates per order status, fixing an issue where an order moving from processing to
  completed sent the same notification twice and was billed twice.

= 1.0.0 =
* First release: Alimtalk, Friendtalk and SMS sending, plus WooCommerce order notifications.

== Upgrade Notice ==

= 1.2.0 =
Kakao Friendtalk was discontinued on 2025-12-31. Requests are automatically delivered as Brand
Message by Kakao, so existing settings keep working, but Brand Message is recommended for new
integrations.
