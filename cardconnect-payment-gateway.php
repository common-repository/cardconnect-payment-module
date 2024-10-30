<?php
	/**
	 * Plugin Name: CardPointe Payment Gateway for WooCommerce
	 * Plugin URI: https://wordpress.org/plugins/cardconnect-payment-module
	 * Description: Accept credit card payments in your WooCommerce store.
	 * Version: 3.4.14
	 * Author: Fiserv <nicole.anderson@fiserv.com>
	 * Author URI: https://cardconnect.com
	 * License: GNU General Public License v2
	 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
	 *
	 * WC requires at least: 5.0
	 * WC tested up to: 6.1.1
	 *
	 * @version 3.4.14
	 * @author  CardPointe/RexAK
	 */

	/*
		Copyright: © 2021 RexAK <rex@ellasol.com>
	*/

	if (!defined('ABSPATH')) {
		exit; // Exit if accessed directly
	}

	define('WC_CARDCONNECT_VER', '3.4.14');
	define('WC_CARDCONNECT_PLUGIN_PATH', untrailingslashit(plugin_basename(__DIR__)));
	define('WC_CARDCONNECT_ASSETS_URL', untrailingslashit(plugin_dir_url(__FILE__)) . '/assets/');
	define('WC_CARDCONNECT_TEMPLATE_PATH', untrailingslashit(plugin_dir_path(__FILE__)) . '/templates/');
	define('WC_CARDCONNECT_PLUGIN_URL', untrailingslashit(plugins_url('', __FILE__)));

	add_action('plugins_loaded', 'CardConnectPaymentGateway_init', 0);
	add_action('wp_ajax_admin_banned_cards', 'admin_banned_cards', 10);
	add_action('admin_enqueue_scripts', 'cc_admin_page_scripts');
	add_filter( 'plugin_action_links_cardconnect-payment-module/cardconnect-payment-gateway.php', 'cc_settings_link' );

	function cc_settings_link( $links ) {
		$url = esc_url( add_query_arg(
			'page',
			'wc-settings&tab=checkout&section=card_connect',
			get_admin_url() . 'admin.php'
		) );
		$settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';
		array_push(
			$links,
			$settings_link
		);
		return $links;
	}
	function cc_admin_page_scripts() {
		wp_enqueue_script('woo-settings', plugins_url('javascript/setting.js', __FILE__), array('jquery'), '3.4.11', true);
		wp_localize_script('woo-settings', 'ajax_var', array(
			'url'   => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('admin_banned_cards')
		));

	}

	function admin_banned_cards() {
		$test = check_ajax_referer('admin_banned_cards', '_ajax_nonce');
		if ($test !== 1) {
			wp_send_json('Nonce failed, refresh the page and try again.');
		}
		if (isset($_POST["card_to_remove"]) && !empty($_POST["card_to_remove"]) && is_array($_POST["card_to_remove"])) {
			$existing = get_option('card_connect_banned_cards');
			foreach ($_POST["card_to_remove"] as $card_num_val) {
				if (in_array($card_num_val, $existing) && is_numeric($card_num_val)) {
					unset($existing[$card_num_val]);
					delete_transient('cc_' . (int)$card_num_val);
				}
			}
			update_option('card_connect_banned_cards', $existing, false);
		} else {
			wp_send_json('Please select a card/token to remove and try again.');
		}
		wp_send_json('refresh');
	}

	/**
	 * Initializes Card Connect Gateway
	 *
	 * @return void
	 * @since 0.5.0
	 */
	function CardConnectPaymentGateway_init() {

		// Append local includes dir to include path
		set_include_path(get_include_path() . PATH_SEPARATOR . plugin_dir_path(__FILE__) . 'includes');

		if (class_exists('CardConnectPaymentGateway') || !class_exists('WC_Payment_Gateway')) {
			return;
		}

		// Include Classes
		include_once 'classes/class-wc-gateway-cardconnect.php';
		include_once 'classes/class-wc-gateway-cardconnect-saved-cards.php';

		// Include Class for WooCommerce Subscriptions extension
		if (class_exists('WC_Subscriptions_Order')) {

			if (!function_exists('wcs_create_renewal_order')) {
				// Subscriptions 1.x
				include_once 'classes/class-wc-gateway-cardconnect-addons-deprecated.php';
			} else {
				// Subscriptions 2.x
				include_once 'classes/class-wc-gateway-cardconnect-addons.php';
			}
		}

		// Include Class for WooCommerce Pre-Orders extension
		if (class_exists('WC_Pre_Orders')) {
			include_once 'classes/class-wc-gateway-cardconnect-addons.php';
		}


		/**
		 * Add the Gateway to WooCommerce
		 **/
		add_filter('woocommerce_payment_gateways', 'woocommerce_add_gateway_CardConnectPaymentGateway');

		function woocommerce_add_gateway_CardConnectPaymentGateway($methods) {


			if (class_exists('WC_Subscriptions_Order')) {
				// handling for WooCommerce Subscriptions extension

				if (!function_exists('wcs_create_renewal_order')) {
					// Subscriptions 1.x
					$methods[] = 'CardConnectPaymentGatewayAddonsDeprecated';
				} else {
					// Subscriptions 2.x
					$methods[] = 'CardConnectPaymentGatewayAddons';
				}

			} elseif (class_exists('WC_Pre_Orders')) {
				// handling for WooCommerce Pre-Orders extension
				$methods[] = 'CardConnectPaymentGatewayAddons';
			} else {
				// handling for plain-ole "simple product" type orders
				$methods[] = 'CardConnectPaymentGateway';
			}

			return $methods;
		}

	}

//	add_action('in_plugin_update_message-cardconnect-payment-module/cardconnect-payment-gateway.php', 'card_connect_update_message', 10, 2);
	function card_connect_update_message($data, $response) {
		if (version_compare(WC_CARDCONNECT_VER, '3.0.0', '>=')) {
			ob_start(); ?>
			<br>Please be advised that CardPointe is in the process of migrating its hosted applications and services.
			<br>
			<li><span style="color:red;">69.164.93.9/32</span></li>
			<li><span style="color:red;">198.62.138.0/24</span></li>
			<li><span style="color:red;">67.217.245.179/32</span></li>
			<li><span style="color:red;">206.201.63.0/24</span></li>
			Contact CardPointe Support at <a
				href="tel:+18778280720">877-828-0720</a> if you have any further questions.
			<?php
			$message = ob_get_clean();
			printf('<br><strong style="">%s</strong>', __($message, 'text-domain'));
		}
	}

	// display CC admin notice


	function wc_cc_notice() {
		$cc_settings_page = admin_url('admin.php?page=wc-settings&tab=checkout&section=card_connect');
		?>

		<div class="notice notice-warning is-dismissible wc-cc-notice">
			<p><?php _e('Please check the <a href="' . $cc_settings_page . '" >CardPointe settings page</a> to set up your CardPointe merchant account.', 'woocommerce'); ?></p>
		</div>

	<?php }


	function cc_add_notice_script() {
		wp_register_script('cc-notice-update', plugins_url('javascript/cc-admin-notice.js', __FILE__), 'jquery', '1.0', false);

		wp_localize_script('cc-notice-update', 'notice_params', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
		));

		wp_enqueue_script('cc-notice-update');
	}

	if (get_option('cc_dismiss_admin_notice') !== 'dismissed') {
		add_action('admin_notices', 'wc_cc_notice');
		add_action('admin_enqueue_scripts', 'cc_add_notice_script');
	}
	add_action('wp_ajax_cc_dismiss_admin_notice', 'cc_dismiss_admin_notice');

	function cc_dismiss_admin_notice() {
		update_option('cc_dismiss_admin_notice', 'dismissed');
	}

	// 2021- required recaptcha
	function recaptcha_update_notice() {

		echo wp_kses('<div class="notice notice-error is-dismissible wc-recaptcha-notice">
             <p><img style="width: 20px;display: inline-block;margin-right: 10px;position: relative;top: 5px;" src="' . WC_CARDCONNECT_ASSETS_URL . 'cardconnect-logo-secondary.gif"/>Version 3.3 of the CardPointe plugin, which will be available on <strong>June 14, 2021</strong>, will include an update to Google ReCaptcha v2 on the checkout form, which will be required to continue accepting payments using the plugin.</p>
             <p>To use the ReCaptcha v2 service, you must sign up and generate the Site Key and Secret Key values, then enter those on the plugin settings page. <a href="https://www.google.com/recaptcha/about/" target="_blank">Click on the v3</a> Admin Console to begin and select v2 when prompted.</p>
         </div>', [
			'img'    => [
				'style' => [],
				'src'   => [],
			],
			'div'    => [
				'class' => [],
				'src'   => [],
			],
			'p'      => [],
			'strong' => [],
		]);
	}

	if (get_option('cc_recaptcha_dismiss_admin_notice') !== 'dismissed') {
		add_action('admin_notices', 'recaptcha_update_notice');
		add_action('admin_enqueue_scripts', 'cc_recaptcha_add_notice_script');
	}
	add_action('wp_ajax_cc_recaptcha_dismiss_admin_notice', 'cc_recaptcha_dismiss_admin_notice');

	function cc_recaptcha_dismiss_admin_notice() {
		update_option('cc_recaptcha_dismiss_admin_notice', 'dismissed');
	}

	function cc_recaptcha_add_notice_script() {
		wp_register_script('cc-recaptcha-update', plugins_url('javascript/cc-recaptcha-notice.js', __FILE__), 'jquery', '1.0', false);
		wp_localize_script('cc-recaptcha-update', 'notice_params', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
		));
		wp_enqueue_script('cc-recaptcha-update');
	}
