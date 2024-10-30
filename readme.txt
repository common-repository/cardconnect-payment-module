=== CardPointe Payment Gateway for WooCommerce ===
Contributors: RexAK
Tags: woocommerce, payment, gateway, cardconnect, cardpointe
Requires at least: 5.1
Tested up to: 5.9
Requires PHP: 7.1
Stable tag: 3.4.14
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
WC requires at least: 5.0+
WC tested up to: 6.1.1
WC Pre-Orders tested with v1.5.30
WC Subscriptions tested with v3.1.4

The CardPointe Payment Gateway allows you to accept Visa, MasterCard, American Express and Discover payments in your WordPress WooCommerce store.

== Description ==
CardPointe payment processing tokenizes sensitive data, safeguarding your customers from a data breach and lessening the burden of PCI compliance.

Businesses that use CardPointe can offer their customers the ability to checkout with a saved card on file, with sensitive data being stored on CardPointe's servers and not the business's systems. The CardPointe plugin supports the WooCommerce Subscription extension.

Click [here](https://support.cardconnect.com/cardpointe/marketplace/woocommerce) for more information.

Please note that WooCommerce (v4.0+) must be installed and active.
The latest version of WooCommerce (v6.1.1) is supported.
The WooCommerce Subscriptions extension (v3.0.x) is fully supported.
PHP should be (v7.1+)
**Please note that ReCaptcha key are required to use this plugin**

You must contact your sales agent to receive the account credentials specifically for this plugin to work. Those credentials are different than what is provided for the Virtual Terminal. Refer to Fiserv's [WooCommerce Support page](https://support.cardconnect.com/cardpointe/marketplace/woocommerce) for more details.

**Security Best Practices**
**While not required, it is strongly recommended that you configure your site to meet the following security best practices:**

* Secure your checkout page with an SSL (secure socket layer) certificate.

* As of version 3.3+, recaptcha is built in and required in live mode, and you must configure your WooCommerce > Payments settings to include ReCapthca keys.

Version 3.3.3 of the plugin includes the following new settings to help prevent fraud and carding events carried out by malicious scripts and bots:

* Maximum Credit Card Attempts
This setting limits the number of authorization attempts for a given payment card. Once the limit is exceeded, the card will be banned from use on the plugin. Must be a value from 3 (default) to 5 attempts.
Banned cards are displayed in the Currently Banned Card Tokens list. To re-enable a banned card token, you can select it and click Delete Selected Tokenized Card(s) to remove it from the list. The default setting is 3 attempts.

* Rate limiting
This setting limits the number of payments a cardholder can attempt to make in a given amount of time. The default setting requires a minimum of 3 seconds between payments.

* Maximum order attempts
This setting limits the number of attempts to submit a payment for a given order. Must be a value from 3 (default) to 10 attempts.

See [Configuring the WooCommerce Plugin](https://support.cardpointe.com/cardpointe/marketplace/woocommerce#configuring-the-wooCommerce-plugin) for more information.

Additionally, we strongly recommend using the iFrame tokenization method for capturing customer payment card numbers. Select Enable IFRAME API on the WooCommerce> Settings >Payments tab to enable the Hosted iFrame Tokenizer on your checkout form for an additional layer of security.  See [Advanced Tokenization Settings](https://support.cardpointe.com/cardpointe/marketplace/woocommerce#advanced-tokenization-settings) for more information.

*Note: If the security of your webpage becomes compromised, Fiserv reserves the right to disable your CardPointe merchant account.*

== Installation ==
* Upload plugin files to your plugins folder, or install using WordPress built-in Add New Plugin installer;
* Activate the plugin;
* Configure the plugin settings in WooCommerce > Settings > Payments > CardPointe
* Contact your sales representative for your merchant ID and credentials, and to activate your account for WooCommerce usage.
* Obtain ReCaptcha V2 keys for your site, from https://www.google.com/recaptcha/admin/ and enter them into the WooCommerce settings for this plugin.

== Frequently Asked Questions ==
= How do I use the new ReCaptcha V2 option? =

First, make sure you have obtained ReCaptcha V2 keys for your site, from https://www.google.com/recaptcha/admin/
Please see https://www.google.com/recaptcha/about/ for more info.
Log in to your WordPress Dashboard and navigate to WooCommerce > Settings > Payments.
Select the "Manage" button, next to the CardPointe payment method.
Select the "Enable Google ReCaptcha on Checkout" option and fill in your ReCaptcha Keys

= Does this plugin require that an SSL certificate be installed? =

It is recommended that you install an SSL certificate on your site for the checkout page, however the plugin does not require it.

= Is there an option for a sandbox account for testing? =

Yes. When you sign-up for a merchant account with CardPointe you will receive credentials for a sandbox account as well as a live account.

= Are there any special requirements needed from my hosting provider? =

You may need to request that your hosting provider open certain ports. Specific instructions will be provided when you activate your CardPointe account.

= Who do I contact if I need assistance? =

For further info or support, contact your Fiserv sales agent.

= Does this support the WooCommerce Subscriptions extension? =

Yes, we support  v2.5.x of the Subscriptions extension.  We highly recommend that you use v2.5.x+ for best results.

= Does this support the WooCommerce Pre-Orders extension? =

Yes.

= Does this support all currencies supported by the WooCommerce store? =

We support all WooCommerce currencies except the Ukrainian Hryvnia.

= Can I switch back to the 2.x method of tokenization, and not use the 3.x+ iframe methods =

Yes. Define WC_CC_ADVANCED as true in your wp-config.php file. Then, open the plugin\'s WooCommerce setting page, and configure the new options. Use at your own risk. This will be removed in a future update.

= Who do I contact for support? =

Support is provided by Fiserv. Before posting to this plugin forum, contact your Fiserv sales agent.

= I have trouble with a saved card =

Cards saved while in test/sandbox mode will NOT carry over when switching to live mode. If you've saved a card in this manner, and receive an error, you need to re-save the card in live mode, under a different label/name. Always use a test WP user account for testing saved cards in sandbox mode, and do not try to use the account or it's saved cards in live mode at a later time.

= Where are the Developer CSS options?

Define WC_CC_ADVANCED as true in your wp-config.php file, and visit the plugins settings in WooCommerce.

= How do I send non-standard checkout page form fields along with my transactions?

First, please note that this is in beta. Report any issues you encounter immediately, in the plugin's support area above.
If we've introduce a breaking change, please revert back to the previous 3.3.2x version and turn off auto plugin updating, until we can get a fix.

You must enter your MID, username and password, and then save, to see the new field selector on the CardPointe settings page.

Refer to the "Include these checkout fields in CardPointe transactions" section now present on the settings page, and select the desired checkout form fields, then re-save.

Any data submitted during checkout matching the fields selected in your settings, will show up in a virtual terminal transaction under "Custom Fields" (e.g. selecting "billing_company" will send the standard WooCommerce checkout field called "billing_company," if a customer fills it out.)

Support for the official WooCommerce Checkout Field Editor plugin is included.

Support for custom user fields in the Subscriptions and Pre-Orders plugins is currently experimental.


== Changelog ==
= 3.4.14 =
* fix: private method called under woo hook

= 3.4.12 =
* fix: delete card button esc fix

= 3.4.11 =
* deleted: cruft example files
* added: settings link on plugins list page
* change: moved inline JQ to separate file

= 3.4.9 =
* various security updates
* added: security added before remote post attempts
* added: security settings now present and active in sandbox mode allowing user testing
* added: user facing output escaping
* added: payload custom field to log front-end submission
* fix: empty phantom banned card issue
* change: refinement of ReCaptcha checks and Recaptcha use during sandbox
* change: refinement post-order creation security method
* compatibility: Tested against WooCommerce 6.1.1 and WP 5.96
* deleted: legacy REST example files
* modified: legacy PEST implementation to add basic ABSPATH die

= 3.3.7 =
* updated: plugin name and references
* compatibility: Tested against WooCommerce 5.9.0

= 3.3.5 =
* updated: README.txt

= 3.3.4 =
* new: security features. check compatibility against woo plugin and add-ons

= 3.3.2 =
* update: readme.txt

= 3.3.1 =
* fix:  issue where some checkout might ignore validation on recaptcha

= 3.3.0 =
* NEW - As of June 14, 2021, ReCaptcha is required in plugin settings to place transactions.
* updated - testing against latest versions of WooCommerce
* updated - testing against latest versions of WooCommerce (official) Subscriptions plugin
* updated - testing against latest versions of WooCommerce (official) Pre-Order plugin
* updated - testing against latest versions of WordPress

= 3.2.19 =
* incremental prep for ReCaptcha requirement

= 3.2.18 =
* small grammar change

= 3.2.17 =
* refined ReCaptcha messaging
* bumped and tested compatibility

= 3.2.16 =
* update notification added for upcoming ReCaptcha requirement

= 3.2.15 =
* updated branding - minor changes

= 3.2.14 =
* updated branding to reflect CardPointe and Fiserv

= 3.2.13 =
* add new COF and COFSCHEDULED params per new API requirements

= 3.2.12 =
* fix - validation when using a different payment method

= 3.2.11 =
* added ReCaptcha for checkout form
* compatibility tested.
* updated readme

= 3.2.9 =
* compatibility tested.
* updated readme

= 3.2.8 =
* compatibility: Tested against WC 4.0.1, WordPress 5.4, Subscriptions 3.0.3, and Pre-Orders 1.5.24.

= 3.2.7 =
* compatibility: Tested against WC 3.9.1, WordPress 5.3.2, Subscriptions 3.0.1, and Pre-Orders 1.5.22.
* Minimum PHP version bump to 7.0
* WP minimum bumped to 5.0

= 3.2.6 =
* compatibility: Tested against WC 3.8, WordPress 5.3, Subscriptions 2.6.4, and Pre-Orders 1.5.20.

= 3.2.5 =
* removed port 8443 usage - ports 6443 and 8443 no longer required

= 3.2.4 =
* compatibility: Tested against WC 3.7.0, WordPress 5.2.3, Subscriptions 2.6.1, and Pre-Orders 1.5.17.
* change: removed previously required ports for UAT

= 3.2.3 =
* compatibility: Tested against WC 3.7.0, WordPress 5.2.2, Subscriptions 2.5.7, and Pre-Orders 1.5.17.
* new: hide form card, exp., and CVV fields when using saved cards

= 3.2.2 =
* small fix for order comments

= 3.2.1 =
* array_merge fix to prevent PHP notice/warning

= 3.2.0 =
* beta: WooCommerce Checkout Form Fields can be included in transactions, as part of the Virtual Terminal Custom user fields
* compatibility: Tested against WooCommerce 3.6.4

= 3.1.4 =
* removed: removed SVN versions prior to 2.0.18
* compatibility: Tested against WooCommerce 3.6.1 - Subscriptions 2.5.3 - Pre-Orders 1.5.13

= 3.1.3 =
* fix: issue with PHP method return value error

= 3.1.2 =
* change: basic CSS defaults, when using non-autostyle settings
* new: added update notification methods
* new: WC_CC_ADVANCED removed in favor of option in CardPointe settings.

= 3.1.1 =
* fix: subscription function fatal error

= 3.1.0 =
* New: Developer CSS options that allow customization of the IFRAME CC number field
* fix: small translation syntax error for
* Tested for latest WooCommerce compatibility (3.5.3)

= 3.0.4 =
* Change: Remove development functions

= 3.0.3 =
* Force update - for those on < 3.0.3

= 3.0.2 =
* repackage, restore raven

= 3.0.1 =
* removes Fatal error reported when upgrading

= 3.0.0 =
* Change: Now tokenizes card numbers via CardPoint\'s iframe methods
* Change: added method to attempt to pull WooCommerce checkout styles, and apply them to the check out card detail fields
* Fix: normalizes the refund total to prevent some isolated cases where refund amounts were multiplied by 100
* Fix: Various tokenization checks for both the new iframe version and older JS tokenization calls
* Fix: Tokenization carries over from previous versions, into this version. Subscriptions and Saved cards are persistent.


== Upgrade Notice ==
= 3.0.0 =
CardPointe Payment Module 3.0.0 is a major release. Please insure compatibility before updating. See the changelog
for update info.

= 2.0.0 =
Major release to fully support the WooCommerce Subscriptions 2.x extension plugin for the WooCommerce store.

= 1.0.1 =
Upgrade for bug fixes

= 1.0.0 =
Initial repository version
