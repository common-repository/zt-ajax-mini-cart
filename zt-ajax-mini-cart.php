<?php
/*
Plugin Name: Ajax mini cart
Description: Ajax mini cart for woocommerce by Artem Koliada. Adds ajax mini cart to your site.
Version: 1.0.2
Author: Artem Koliada
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.txt
Text Domain: zt-ajax-mini-cart
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // don't access directly
};

define( 'ZT_AJAX_MINI_CART_PATH', plugin_dir_path( __FILE__ ) );
define( 'ZT_AJAX_MINI_CART_URL', plugin_dir_url( __FILE__ ) );

class ZT_Ajax_Mini_Cart {
	public function __construct() {
		$this->autoload();
	}

	public function autoload() {
		require_once ZT_AJAX_MINI_CART_PATH . '/src/zt-enqueue.php';
		require_once ZT_AJAX_MINI_CART_PATH . '/src/zt-admin.php';
		require_once ZT_AJAX_MINI_CART_PATH . '/src/zt-front-end.php';
	}
}

new ZT_Ajax_Mini_Cart();