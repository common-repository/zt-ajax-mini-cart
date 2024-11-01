<?php

class ZT_Ajax_Mini_Cart_Enqueue {

	public function __construct() {
		$this->hooks();
	}

	public function hooks() {
		// Front End Enqueue Scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'front_enqueue_scripts' ) );

		// Admin Enqueue Scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	public function front_enqueue_scripts() {
		if ( ( is_admin() ) ) { return; }

		wp_enqueue_script('zt-mini-cart-script', ZT_AJAX_MINI_CART_URL . 'assets/js/main.js', array('jquery') );

		wp_localize_script('zt-mini-cart-script', 'mini_cart_data',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'site_url' => ZT_AJAX_MINI_CART_URL
			)
		);

		if ( 'off' !== _x( 'on', 'Google font: on or off', 'zt-ajax-mini-cart' ) ) {
			$fonts = array(
				'Montserrat:400,700',
			);

			$font_url = add_query_arg( 'family', ( implode( '|', $fonts ) . "&subset=latin,latin-ext" ), "//fonts.googleapis.com/css" );

			wp_enqueue_style( 'main-fonts', $font_url, array() );
		}

		wp_enqueue_style( 'zt-mini-cart-style', ZT_AJAX_MINI_CART_URL . 'assets/css/style.min.css' );
		wp_enqueue_style( 'zt-dynamic-css', admin_url( 'admin-ajax.php' ) . '?action=dynamic_css' );
	}

	public function admin_enqueue_scripts() {

		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script('zt-mini-cart-admin-script', ZT_AJAX_MINI_CART_URL . 'assets/js/admin.js', array('jquery'), '1.0.0', true );

		wp_enqueue_style( 'wp-color-picker' );
	}
}

new ZT_Ajax_Mini_Cart_Enqueue();