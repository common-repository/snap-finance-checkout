<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              snapfinance.com
 * @since             2.1.6
 * @package           snap_finance_checkout
 *
 * @wordpress-plugin
 * Plugin Name:       Snap Finance
 * Plugin URI:        https://developer.snapfinance.com/woocommerce/
 * Description:       Credit-challenged? Snap approves $300 up to $5,000 in accessible lease-to-own financing.
 * Version:           2.1.13
 * Author:            Snap Finance
 * Author URI:        https://snapfinance.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       snap-finance-checkout
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WOOCOMMERCE_GATEWAY_SNAP_FINANCE_VERSION', '1.0.4' );

/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter( 'woocommerce_payment_gateways', 'snap_finance_add_gateway_class' );

/**
 * Method to return the snap finance $gateways class
 *
 * @param array $gateways  Your class name.
 */
function snap_finance_add_gateway_class( $gateways ) {
	$gateways[] = 'WC_snap_finance_Gateway'; // your class name is here.

	return $gateways;
}

$activated = true;
if ( function_exists( 'is_multisite' ) && is_multisite() ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
	if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		$activated = false;
	}
} else {
	if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		$activated = false;
	}
}

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
if ( $activated ) {
	add_action( 'plugins_loaded', 'snap_finance_init_gateway_class' );
} else {
	if ( ! function_exists( 'deactivate_plugins' ) ) {
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
	}
	deactivate_plugins( plugin_basename( __FILE__ ) );
	add_action( 'admin_notices', 'snap_finance_checkout_error_notice' );
}

function deactivate_snap_finance_checkout() {
	$plugin_data    = get_plugin_data( __FILE__ );
	$plugin_version = $plugin_data['Version'];
	$to             = 'devsupport@snapfinance.com';
	$subject        = 'Disabled WordPress plugin : ' . site_url();
	$body           = '<p>Following Merchant has disabled plugin</p>
	<p>Merchant URL: ' . site_url() . '</p><p>Plugins Version: ' . $plugin_version . '</p>';
	$headers        = array(
		'Content-Type: text/html; charset=UTF-8',
		'From: Do Not Reply <devsupport@snapfinance.com>',
	);
	wp_mail( $to, $subject, $body, $headers );
}

function shiping_round_option_mail() {
	$plugin_data     = get_plugin_data( __FILE__ );
	$payment_setting = get_option( 'woocommerce_snap_finance_settings' );
	$plugin_version  = $plugin_data['Version'];
	$to              = 'devsupport@snapfinance.com';
	$subject         = 'Roundup is ON';
	$body            = '<p>Store: ' . site_url() . '</p>';
	if ( isset( $payment_setting['snap_finance_client_live_id'] ) ) {
		$body .= '<p>Merchant ID: ' . $payment_setting['snap_finance_client_live_id'] . '</p>';
	}
	$headers = array( 'Content-Type: text/html; charset=UTF-8' );
	wp_mail( $to, $subject, $body, $headers );
}

register_deactivation_hook( __FILE__, 'deactivate_snap_finance_checkout' );

function activate_snap_finance_checkout() {
	$plugin_data    = get_plugin_data( __FILE__ );
	$plugin_version = $plugin_data['Version'];
	$to             = 'devsupport@snapfinance.com';
	$subject        = 'Activated WordPress plugin : ' . site_url();
	$body           = '<p>Following Merchant has activated plugin</p>
	<p>Merchant URL: ' . site_url() . '</p><p>Plugins Version: ' . $plugin_version . '</p>';
	$headers        = array(
		'Content-Type: text/html; charset=UTF-8',
		'From: Do Not Reply <devsupport@snapfinance.com>',
	);
	wp_mail( $to, $subject, $body, $headers );
	if ( get_option( 'woocommerce_tax_round_at_subtotal' ) == 'yes' ) {
		shiping_round_option_mail();
	}
}

register_activation_hook( __FILE__, 'activate_snap_finance_checkout' );

function snap_finance_checkout_error_notice() {
	?>
	<div class="error notice is-dismissible">
		<p><?php _e( 'Woocommerce is not activated, Please activate Woocommerce first to install Snap Finance Checkout.', 'snap-finance-checkout' ); ?></p>
	</div>
	<style>
		#message{display:none;}
	</style>
	<?php
}


function snap_finance_init_gateway_class() {

	//include 'config.php';
	include(__DIR__.'/config.php');

	include(__DIR__.'/snap-finance-functions.php');

	include(__DIR__.'/snap-finance-wc-order.php');

	include(__DIR__.'/snap-finance-payment-class.php');
}
