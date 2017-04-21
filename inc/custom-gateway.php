<?php
if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

function add_custom_gateway( $gateways ) {
	$gateways[] = 'WC_Gateway_Custom';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'add_custom_gateway' );

add_action( 'plugins_loaded', 'wc_custom_gateway_init', 11 );
function wc_custom_gateway_init() {
	/**
	 * Custom Gateway.
	 *
	 * My Custom GateWay
	 */
	class WC_Gateway_Custom extends WC_Payment_Gateway {

		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {
			// Setup general properties
			$this->setup_properties();

			// Load the settings
			$this->init_form_fields();
			$this->init_settings();

			// Get settings
			$this->title              = $this->get_option( 'title' );
			$this->description        = $this->get_option( 'description' );
			$this->instructions       = $this->get_option( 'instructions' );
			// $this->enable_for_methods = $this->get_option( 'enable_for_methods', array() );
			// $this->enable_for_virtual = $this->get_option( 'enable_for_virtual', 'yes' ) === 'yes' ? true : false;

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );

			// Customer Emails
			add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
		}

		/**
		 * Setup general properties for the gateway.
		 */
		protected function setup_properties() {
			$this->id                 = 'after_confirm';
			$this->icon               = '';
			$this->method_title       = __( 'Оплата после подтверждения', 'woocommerce' );
			$this->method_description = __( 'Оплата возможна только после подтверждения Менаджером.', 'woocommerce' );
			$this->has_fields         = false;
		}

		/**
		 * Initialise Gateway Settings Form Fields.
		 */
		public function init_form_fields() {
			$shipping_methods = array();

			foreach ( WC()->shipping()->load_shipping_methods() as $method ) {
				$shipping_methods[ $method->id ] = $method->get_method_title();
			}

			$this->form_fields = array(
				'enabled' => array(
					'title'       => __( 'Enable/Disable', 'woocommerce' ),
					'label'       => __( 'Включить оплату после подтверждения', 'woocommerce' ),
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no',
				),
				'title' => array(
					'title'       => __( 'Title', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Payment method description that the customer will see on your checkout.', 'woocommerce' ),
					'default'     => __( 'Оплата после подтверждения', 'woocommerce' ),
					'desc_tip'    => true,
				),
				'description' => array(
					'title'       => __( 'Description', 'woocommerce' ),
					'type'        => 'textarea',
					'description' => __( 'Payment method description that the customer will see on your website.', 'woocommerce' ),
					'default'     => __( 'Оплата после согласования', 'woocommerce' ),
					'desc_tip'    => true,
				),
				'instructions' => array(
					'title'       => __( 'Instructions', 'woocommerce' ),
					'type'        => 'textarea',
					'description' => __( 'Instructions that will be added to the thank you page.', 'woocommerce' ),
					'default'     => __( 'После подтверждения заказа с вами свяжется наш менаджер для уточнения заказа.', 'woocommerce' ),
					'desc_tip'    => true,
				),
				// 'enable_for_methods' => array(
				// 	'title'             => __( 'Enable for shipping methods', 'woocommerce' ),
				// 	'type'              => 'multiselect',
				// 	'class'             => 'wc-enhanced-select',
				// 	'css'               => 'width: 400px;',
				// 	'default'           => '',
				// 	'description'       => __( 'If COD is only available for certain methods, set it up here. Leave blank to enable for all methods.', 'woocommerce' ),
				// 	'options'           => $shipping_methods,
				// 	'desc_tip'          => true,
				// 	'custom_attributes' => array(
				// 		'data-placeholder' => __( 'Select shipping methods', 'woocommerce' ),
				// 	),
				// ),
				// 'enable_for_virtual' => array(
				// 	'title'             => __( 'Accept for virtual orders', 'woocommerce' ),
				// 	'label'             => __( 'Accept COD if the order is virtual', 'woocommerce' ),
				// 	'type'              => 'checkbox',
				// 	'default'           => 'yes',
				// ),
		   );
		}

		/**
		 * Check If The Gateway Is Available For Use.
		 *
		 * @return bool
		 */
		public function is_available() {
			// $order          = null;
			// $needs_shipping = false;

			// // Test if shipping is needed first
			// if ( WC()->cart && WC()->cart->needs_shipping() ) {
			// 	$needs_shipping = true;
			// } elseif ( is_page( wc_get_page_id( 'checkout' ) ) && 0 < get_query_var( 'order-pay' ) ) {
			// 	$order_id = absint( get_query_var( 'order-pay' ) );
			// 	$order    = wc_get_order( $order_id );

			// 	// Test if order needs shipping.
			// 	if ( 0 < sizeof( $order->get_items() ) ) {
			// 		foreach ( $order->get_items() as $item ) {
			// 			$_product = $item->get_product();
			// 			if ( $_product && $_product->needs_shipping() ) {
			// 				$needs_shipping = true;
			// 				break;
			// 			}
			// 		}
			// 	}
			// }

			// $needs_shipping = apply_filters( 'woocommerce_cart_needs_shipping', $needs_shipping );

			// // Virtual order, with virtual disabled
			// if ( ! $this->enable_for_virtual && ! $needs_shipping ) {
			// 	return false;
			// }

			// Check methods
			// if ( ! empty( $this->enable_for_methods ) && $needs_shipping ) {

			// 	// Only apply if all packages are being shipped via chosen methods, or order is virtual
			// 	$chosen_shipping_methods_session = WC()->session->get( 'chosen_shipping_methods' );

			// 	if ( isset( $chosen_shipping_methods_session ) ) {
			// 		$chosen_shipping_methods = array_unique( $chosen_shipping_methods_session );
			// 	} else {
			// 		$chosen_shipping_methods = array();
			// 	}

			// 	$check_method = false;

			// 	if ( is_object( $order ) ) {
			// 		if ( $order->shipping_method ) {
			// 			$check_method = $order->shipping_method;
			// 		}
			// 	} elseif ( empty( $chosen_shipping_methods ) || sizeof( $chosen_shipping_methods ) > 1 ) {
			// 		$check_method = false;
			// 	} elseif ( sizeof( $chosen_shipping_methods ) == 1 ) {
			// 		$check_method = $chosen_shipping_methods[0];
			// 	}

			// 	if ( ! $check_method ) {
			// 		return false;
			// 	}

			// 	$found = false;

			// 	foreach ( $this->enable_for_methods as $method_id ) {
			// 		if ( strpos( $check_method, $method_id ) === 0 ) {
			// 			$found = true;
			// 			break;
			// 		}
			// 	}

			// 	if ( ! $found ) {
			// 		return false;
			// 	}
			// }

			return parent::is_available();
		}


		/**
		 * Process the payment and return the result.
		 *
		 * @param int $order_id
		 * @return array
		 */
		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );

			// Mark as processing or on-hold (payment won't be taken until delivery)
			$order->update_status( apply_filters( 'woocommerce_cod_process_payment_order_status', $order->has_downloadable_item() ? 'on-hold' : 'processing', $order ), __( 'Payment to be made upon delivery.', 'woocommerce' ) );

			// Reduce stock levels
			wc_reduce_stock_levels( $order_id );

			// Remove cart
			WC()->cart->empty_cart();

			// Return thankyou redirect
			return array(
				'result' 	=> 'success',
				'redirect'	=> $this->get_return_url( $order ),
			);
		}

		/**
		 * Output for the order received page.
		 */
		public function thankyou_page() {
			if ( $this->instructions ) {
				echo wpautop( wptexturize( $this->instructions ) );
			}
		}

		/**
		 * Add content to the WC emails.
		 *
		 * @access public
		 * @param WC_Order $order
		 * @param bool $sent_to_admin
		 * @param bool $plain_text
		 */
		public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
			if ( $this->instructions && ! $sent_to_admin && $this->id === $order->get_payment_method() ) {
				echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
			}
		}
	}
}