<?php
/*
Plugin Name:  WooCommerce Email Digest
Plugin URI:   https://www.daveramsey.com
Description:  Compiles a list of recent events/transactions from WooCommerce and emails the designated recipient on a recurring schedule.
Version:      1.0.0
Author:       Philip Downer <philip.downer@daveramsey.com>
Author URI:   https://philipdowner.com
License:      GPLv3
License URI:  https://www.gnu.org/licenses/gpl-3.0.html
*/

//Disallow direct file access
if( !defined('ABSPATH') ) {
	die('Direct file access is not allowed.');
}

//Set version constraints
define('WCED_REQUIRED_PHP_VERSION', '7.1');
define('WCED_REQUIRED_WP_VERSION', '4.6');
define('WCED_REQUIRED_WC_VERSION', '3.4');
define('WCED_REQUIRED_WCS_VERSION', '2.2.9');

if( wcEmailDigest_requirementsMet() ) {
	//Require needed files
	if( !class_exists('WC_Emails') ) {
		include_once(plugin_dir_path(__DIR__) . 'woocommerce/includes/class-wc-emails.php');
	}

	if( !class_exists('WC_Email') ) {
		include_once(plugin_dir_path(__DIR__) . 'woocommerce/includes/emails/class-wc-email.php');
	}

	require_once(plugin_dir_path(__FILE__) . '/vendor/autoload.php');
	
	//Hook into available WC Emails
	add_filter('woocommerce_email_classes', 'wcEmailDigest_add_new_subscription_digest', 10, 1);

	//Send emails on recurring basis
	add_action(WCEmailDigest\NewSubscriptionsDigestEmail::$cronHook, 'wcEmailDigest_send_new_subscribers_email');
	add_action(WCEmailDigest\CancelledSubscriptionsDigestEmail::$cronHook, 'wcEmailDigest_send_cancelled_subscribers_email');
}
else {
	add_action('admin_notices', 'wcEmailDigest_requirements_error');
}

/**
 * Check if plugin version requirements met
 * @return bool
 */
function wcEmailDigest_requirementsMet() {
	global $wp_version;
	require_once( ABSPATH . '/wp-admin/includes/plugin.php');

	if( version_compare(PHP_VERSION, WCED_REQUIRED_PHP_VERSION, '<') ) return false;
	if( version_compare($wp_version, WCED_REQUIRED_WP_VERSION, '<') ) return false;

	if( !is_plugin_active('woocommerce/woocommerce.php') || !is_plugin_active('woocommerce-subscriptions/woocommerce-subscriptions.php') ) return false;

	$wcData = get_plugin_data(WP_PLUGIN_DIR . '/woocommerce/woocommerce.php');
	if( version_compare($wcData['Version'], WCED_REQUIRED_WC_VERSION, '<') ) return false;

	$wcsData = get_plugin_data(WP_PLUGIN_DIR . '/woocommerce-subscriptions/woocommerce-subscriptions.php');
	if( version_compare($wcsData['Version'], WCED_REQUIRED_WCS_VERSION, '<') ) return false;

	return true;
}

/**
 * Show an admin notice if version requirements not met
 * @return void
 */
function wcEmailDigest_requirements_error() {
?>
<div class="notice notice-error">
	<p>WooCommerce Email Digest requires that the WooCommerce (v<?php echo WCED_REQUIRED_WC_VERSION; ?>+) and WooCommerce Subscriptions (v<?php echo WCED_REQUIRED_WCS_VERSION; ?>+) plugins be active. You must also be running PHP v<?php echo WCED_REQUIRED_PHP_VERSION; ?> or higher.</p>
</div>
<?php
}

/**
 * Filter the available WooCommerce Emails
 * @param  array $classes
 * @return array
 */
function wcEmailDigest_add_new_subscription_digest($classes) {
	$classes['NewSubscriptionsDigestEmail'] = new WCEmailDigest\NewSubscriptionsDigestEmail();
	$classes['CancelledSubscriptionsDigestEmail'] = new WCEmailDigest\CancelledSubscriptionsDigestEmail();
	return $classes;
}

/**
 * Send the New Subscriptions email. Triggered via CRON.
 * @return void
 */
function wcEmailDigest_send_new_subscribers_email() {
	$emails = WC_Emails::instance();
	$email = new WCEmailDigest\NewSubscriptionsDigestEmail();
	$email->trigger();
}

/**
 * Send the Cancelled Subscriptions email. Triggered via CRON.
 * @return void
 */
function wcEmailDigest_send_cancelled_subscribers_email() {
	$emails = WC_Emails::instance();
	$email = new WCEmailDigest\CancelledSubscriptionsDigestEmail();
	$email->trigger();
}