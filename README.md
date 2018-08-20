# WooCommerce Batched Emails

Sends an once daily email digest to the designated recipient with information pertaining to transactions and activity in the system. Currently supports the following:

## WooCommerce Subscriptions

* A list of all new subscriptions.
* A list of all cancelled subscriptions.

### Requirements
* PHP v7.1+
* WordPress v4.6+
* WooCommerce v3.4+
* WooCommerce Subscriptions v2.2.9+

### Usage

1. Ensure that the requirements above are met, and that the required plugins are active.
1. Install and then activate this plugin from the WordPress admin panel. If version constraints are not met, an admin notice will be displayed.
1. Navigate to the `WooCommerce -> Settings` menu and click the `Emails` tab. Emails added by this plugin will have the name "Digest" included, eg. "New Subscription Digest".
1. Click a digest email to manage it's settings. You can enable/disable the email, specify the message recipient(s) and choose how often you'd like to receive the digest.