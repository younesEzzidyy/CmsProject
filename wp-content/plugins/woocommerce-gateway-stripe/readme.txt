=== WooCommerce Stripe Payment Gateway ===
Contributors: woocommerce, automattic, royho, akeda, mattyza, bor0, woothemes
Tags: credit card, stripe, apple pay, payment request, google pay, sepa, bancontact, alipay, giropay, ideal, p24, woocommerce, automattic
Requires at least: 6.5
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 9.0.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Attributions: thorsten-stripe

Take credit card payments on your store using Stripe.

== Description ==

Changing consumer behavior has resulted in an explosion of payment methods and experiences, which are great for increasing conversion and lowering costs—but can be difficult for businesses to maintain. Give customers a best-in-class checkout experience while you remain focused on your core business. This is the official plugin created by Stripe and WooCommerce.

= Drive 11.9% in revenue with an optimized checkout experience from Stripe =

The enhanced checkout experience from Stripe can help customers:

- **Boost conversion:** Provide an optimal experience across mobile, tablet, and desktop with a responsive checkout, and offer 23 payment methods, including [Link](https://stripe.com/payments/link), [Apple Pay](https://woocommerce.com/apple-pay/), and [Google Pay](https://www.google.com/payments/solutions/), out of the box.
- **Expand your customer base:** Convert customers who might otherwise abandon their cart with buy now, pay later methods like Klarna, Affirm, and Afterpay/Clearpay, wallets like Apple Pay, Google Pay, Alipay, and WeChat Pay, and local payment methods such as Bancontact in Europe and Alipay in Asia Pacific. Deliver a localized payment experience with out-of-the-box support for localized error messages, right-to-left languages, and automatic adjustment of input fields based on payment method and country.
- **Meet existing customer demand and localize the experience:** Offer [local payment methods](https://stripe.com/guides/payment-methods-guide), such as Bancontact, Boleto, Cash App Pay, EPS, giropay, iDEAL, Multibanco, OXXO, Przelewy 24, and SEPA Direct Debit.
- **Fight fraud:** Detect and prevent fraud with [Stripe Radar](https://stripe.com/radar), which offers seamlessly integrated, powerful fraud-detection tools that use machine learning to detect and flag potentially fraudulent transactions.
- **Accept in-person payments for products and services:** Use the Stripe Terminal M2 card reader or get started with no additional hardware using Tap to Pay on iPhone, or Tap to Pay on Android.
- **Support subscriptions:** Support recurring payments with various payment methods via [WooCommerce Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/).
- **Manage cash flow:** Get paid within minutes with Stripe Instant Payouts, if eligible.
- **Achieve [PCI-DSS](https://docs.stripe.com/security) compliance with [Stripe Elements](https://stripe.com/payments/elements) hosted input fields.**
- Support Strong Customer Authentication (SCA).

Stripe is available for store owners and merchants in [46 countries worldwide](https://stripe.com/global), with more to come.

== Frequently Asked Questions ==

= In which specific countries is Stripe available? =

Stripe is available in the following countries, with more to come:

- Australia
- Austria
- Belgium
- Brazil
- Bulgaria
- Canada
- Croatia
- Cyprus
- Czech Republic
- Denmark
- Estonia
- Finland
- France
- Germany
- Gibraltar
- Greece
- Hong Kong
- Hungary
- India
- Ireland
- Italy
- Japan
- Latvia
- Liechtenstein
- Lithuania
- Luxembourg
- Malaysia
- Malta
- Mexico
- Netherlands
- New Zealand
- Norway
- Poland
- Portugal
- Romania
- Singapore
- Slovakia
- Slovenia
- Spain
- Sweden
- Switzerland
- Thailand
- United Arab Emirates
- United Kingdom
- United States

= Does this require an SSL certificate? =

Yes. In Live Mode, an SSL certificate must be installed on your site to use Stripe. In addition to SSL encryption, Stripe provides an extra JavaScript method to secure card data using [Stripe Elements](https://stripe.com/elements).

= Does this support both production mode and sandbox mode for testing? =

Yes, it does. Both production and test (sandbox) modes are driven by the API keys you use with a checkbox in the admin settings to toggle between both.

= Where can I find documentation? =

Refer to the [Stripe WooCommerce Extension documentation for more information, including how to set up and configure the extension](https://woocommerce.com/document/stripe/).

= Where can I get support or talk to other users? =

If you get stuck, you can ask for help in the [Plugin Forum](https://wordpress.org/support/plugin/woocommerce-gateway-stripe/).

== Screenshots ==

1. With the enhanced checkout from Stripe, you can surface 23 payment methods including buy now, pay later methods; and Link, an accelerated checkout experience.
2. Link autofills your customers’ payment information to create an easy and secure checkout experience.
3. Convert customers who would usually abandon their cart and increase average order value with buy now, pay later options like Klarna, Afterpay, and Affirm. Accept credit and debit card payments from Visa, Mastercard, American Express, Discover, and Diners.
4. Stripe Radar offers seamlessly integrated, powerful fraud-detection tools that use machine learning to detect and flag potentially fraudulent transactions.
5. Accept in-person payments for products and services using the Stripe Terminal M2 card reader.
6. Get started with no additional hardware using Tap to Pay on iPhone, or Tap to Pay on Android.

== Changelog ==

= 9.0.0 - 2024-12-12 =
* Fix - Fix 404 that happens when using ECE and 3D Secure auth is triggered.
* Fix - Set correct payment method when using Link and a card that is 3D Secure authenticated.
* Fix - Fix total calculation for custom product types when using the Payment Request Button.
* Fix - Fix order attribution metadata not included in PRBs or Express Checkout Element.
* Add - Pre-fill user email and phone number for Link in the Payment Element.
* Remove - Remove Link autofill modal feature.
* Update - Improve accuracy of webhook status information displayed in settings page.
* Tweak - Standardize ECE Express payment buttons on Pay for Order page to match cart and checkout itemization behavior.
* Tweak - Remove duplicate notice about the new checkout experience.
* Tweak - Include page URL information in the SSL-required log for the Stripe Express Checkout Element.
* Fix - Fix ECE modal not loading on pay for order page when coupon is applied.
* Fix - Do not load express payment buttons on switch subscription page.
* Fix - Resolve a fatal error by casting product price and subscription sign up fee to 'float' while adding them.
* Fix - Return 'is_live' as true in account summary response when test mode is disabled in gateway settings and charge is enabled in Stripe account.
* Fix - Prevents notices being displayed on WordPress 6.7 due to loading translations too early (only shown on stores with WP_DEBUG enabled).
* Fix - Prevent showing the shipping options on express checkout modal for virtual product variations.
* Fix - Do not assume payment is using a saved card when retrying a failed payment.
* Tweak - Update links to plugin documentation and Stripe documentation.

[See changelog for all versions](https://raw.githubusercontent.com/woocommerce/woocommerce-gateway-stripe/trunk/changelog.txt).
