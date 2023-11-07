<?php

class WCQuickOrderHandler {

	public function __construct() {
		add_action( 'init', [ $this, 'registerUrl' ], 90 );
		add_filter( 'query_vars', [ $this, 'registerQueryVar' ] );
		add_action( 'template_redirect', [ $this, 'orderProcess' ] );
	}

	public function registerUrl() {
		add_rewrite_rule(
			'^quick-order/([^/]*)/?',
			'index.php?quick_order=1&product_id=$matches[1]',
			'top'
		);
	}

	public function registerQueryVar( $query_vars ) {
		$query_vars[] = 'quick_order';
		$query_vars[] = 'product_id';

		return $query_vars;
	}

	/**
	 * @throws Exception
	 */
	public function orderProcess() {

		if ( get_query_var( 'quick_order' ) ) {
			$product_id  = get_query_var( 'product_id' );
			$coupon_code = ! empty( $_GET['COUPON'] ) ? sanitize_text_field( $_GET['COUPON'] ) : null;

			$this->addToCart( $product_id, $coupon_code );

			exit;
		}
	}

	/**
	 * @throws Exception
	 */
	private function addToCart( $product_id, $coupon_code ) {
		$cart = WC()->cart;
		$cart->empty_cart();
		$cart->add_to_cart( $product_id, 1 );

		if ( $coupon_code ) {
			$cart->apply_coupon( $coupon_code );
		}

		wp_safe_redirect( wc_get_checkout_url() );
	}

}

new WCQuickOrderHandler();
