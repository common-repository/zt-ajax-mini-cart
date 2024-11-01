<?php

class ZT_Ajax_Mini_Cart_Front_End {
	public function __construct() {
		$this->hooks();
	}

	public function hooks() {
		add_action( 'wp_footer', array( $this, 'output_mini_cart' ) );

		add_action( 'woocommerce_before_quantity_input_field', array( $this, 'add_before_quantity_field' ) );
		add_action( 'woocommerce_after_quantity_input_field', array( $this, 'add_after_quantity_field' ) );

		add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'update_cart_fragments' ), 10, 1 );

		if ( wp_doing_ajax() ) {
			add_action( 'wp_ajax_nopriv_dynamic_css', array( $this, 'dynamic_css' ) );
			add_action( 'wp_ajax_dynamic_css', array( $this, 'dynamic_css' ) );

			add_action( 'wp_ajax_nopriv_empty_mini_cart', array( $this, 'empty_mini_cart' ) );
			add_action( 'wp_ajax_empty_mini_cart', array( $this, 'empty_mini_cart' ) );

			add_action( 'wp_ajax_nopriv_remove_product_in_mini_cart', array( $this, 'remove_product_in_mini_cart' ) );
			add_action( 'wp_ajax_remove_product_in_mini_cart', array( $this, 'remove_product_in_mini_cart' ) );

			add_action( 'wp_ajax_nopriv_update_quantity_product', array( $this, 'update_quantity_product' ) );
			add_action( 'wp_ajax_update_quantity_product', array( $this, 'update_quantity_product' ) );
		}
	}

	public function dynamic_css() {
		require_once ZT_AJAX_MINI_CART_PATH . '/assets/css/custom.css.php';
		wp_die();
	}

	/**
	 * Output mini cart html.
	 */
	public function output_mini_cart() {
		$link_class = WC()->cart->get_cart_contents_count() >= 1 ? 'js-empty-cart' : 'zt-mini-cart__link--disabled';

		$output = '';

		$output .= '<span class="zt-mini-cart__btn">
						<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve">
							<path d="M60,42V12H7V2c0-0.553-0.448-1-1-1H1C0.448,1,0,1.447,0,2s0.448,1,1,1h4v9v1v28v1v7c0,0.553,0.448,1,1,1h7.031
								C11.806,50.912,11,52.359,11,54c0,2.757,2.243,5,5,5s5-2.243,5-5c0-1.641-0.806-3.088-2.031-4h21.062C38.806,50.912,38,52.359,38,54
								c0,2.757,2.243,5,5,5s5-2.243,5-5c0-1.641-0.806-3.088-2.031-4H52c0.552,0,1-0.447,1-1s-0.448-1-1-1H7v-6H60z M16,57
								c-1.654,0-3-1.346-3-3s1.346-3,3-3s3,1.346,3,3S17.654,57,16,57z M43,57c-1.654,0-3-1.346-3-3s1.346-3,3-3s3,1.346,3,3
								S44.654,57,43,57z M58,40H7V14h51V40z"/>
						</svg>
						<span class="zt-mini-cart__count">' . WC()->cart->get_cart_contents_count() . '</span>
					</span>';

		$output .= '<div class="zt-mini-cart">';
			$output .= '<div class="zt-mini-cart__content">';
				$output .= '<h2 class="zt-mini-cart__heading">' . esc_html__('Cart', 'zt-ajax-mini-cart') . '</h2>';
				$output .= '<span class="zt-mini-cart__close"><i></i></span>';
				$output .= '<div class="zt-mini-cart__list">';
					if ( ! WC()->cart->is_empty() ) {
						foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
							$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
							$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

							if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) :
								$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
								$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
								$product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
								$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );

								$output .= '<div class="zt-mini-cart__item ' . esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ) . '">';
								$output .= apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
									'<a href="%s" class="zt-mini-cart__remove-item" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s">&times;</a>',
									esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
									__( 'Remove this item', 'zt-ajax-mini-cart' ),
									esc_attr( $product_id ),
									esc_attr( $cart_item_key ),
									esc_attr( $_product->get_sku() )
								), $cart_item_key );

								if ( $_product->is_sold_individually() ) {
									$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
								} else {
									$product_quantity = $this->quantity_input( array(
										'input_name'   => "cart[{$cart_item_key}][qty]",
										'input_value'  => $cart_item['quantity'],
										'max_value'    => $_product->get_max_purchase_quantity(),
										'min_value'    => '1',
										'product_name' => $_product->get_name(),
									), $_product, false );
								}

								if ( empty( $product_permalink ) ) :
									$output .= $thumbnail;
									$output .= '<div class="zt-mini-cart__info">';
										$output .= '<h4>' . $product_name . '</h4>';
										$output .= $product_price;
										$output .= apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
									$output .= '</div>';
								else :
									$output .= $thumbnail;
									$output .= '<div class="zt-mini-cart__info">';
										$output .= '<h4><a href="' . esc_url( $product_permalink ) . '">' . $product_name . '</a></h4>';
										$output .= $product_price;
										$output .= apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
									$output .= '</div>';
								endif;

								wc_get_formatted_cart_item_data( $cart_item );
								$output .= '</div>';
							endif;
						endforeach;
					} else {
						$output .= '<p>' . esc_html__('No products in the cart.', 'zt-ajax-mini-cart') . '</p>';
					}
				$output .= '</div>';

				$output .= '<div class="zt-mini-cart__footer">';
					$output .= '<div class="zt-mini-cart__subtotal">' . WC()->cart->get_cart_subtotal() . '</div>';
					$output .= '<a href="' . wc_get_cart_url() . '" class="zt-mini-cart__link">' . esc_html__('View Cart', 'zt-ajax-mini-cart') . '</a>';
					$output .= '<a href="#" class="zt-mini-cart__link ' . esc_attr( $link_class ) . '">' . esc_html__('Empty Cart', 'zt-ajax-mini-cart') . '</a>';
				$output .= '</div>';
			$output .= '</div>';
		$output .= '</div>';

		echo $output;
	}

	/**
	 * Remove all product in the cart.
	 */
	public function empty_mini_cart() {
		global $woocommerce;

		$responce_data = array();

		if ( isset( $_POST['empty-cart'] ) ) {
			$woocommerce->cart->empty_cart();

			$responce_data['subtotal'] = WC()->cart->get_cart_subtotal();
			$responce_data['message'] = 'No products in the cart.';
		}

		echo json_encode( $responce_data );

		wp_die();
	}

	/**
	 * Update count fragments.
	 * @param $fragments
	 *
	 * @return mixed
	 */
	public function update_cart_fragments( $fragments ) {
		$fragments['span.zt-mini-cart__count'] = '<span class="zt-mini-cart__count">' . WC()->cart->get_cart_contents_count() . '</span>';

		$link_class = WC()->cart->get_cart_contents_count() >= 1 ? 'js-empty-cart' : 'zt-mini-cart__link--disabled';

		$output = '';

		$output .= '<div class="zt-mini-cart">';
			$output .= '<div class="zt-mini-cart__content">';
				$output .= '<h2 class="zt-mini-cart__heading">' . esc_html__('Cart', 'zt-ajax-mini-cart') . '</h2>';
				$output .= '<span class="zt-mini-cart__close"><i></i></span>';
				$output .= '<div class="zt-mini-cart__list">';
					if ( ! WC()->cart->is_empty() ) {
						foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
							$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
							$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

							if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) :
								$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
								$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
								$product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
								$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );

								$output .= '<div class="zt-mini-cart__item ' . esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ) . '">';
								$output .= apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
									'<a href="%s" class="zt-mini-cart__remove-item" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s">&times;</a>',
									esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
									__( 'Remove this item', 'zt-ajax-mini-cart' ),
									esc_attr( $product_id ),
									esc_attr( $cart_item_key ),
									esc_attr( $_product->get_sku() )
								), $cart_item_key );

								if ( $_product->is_sold_individually() ) {
									$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
								} else {
									$product_quantity = $this->quantity_input( array(
										'input_name'   => "cart[{$cart_item_key}][qty]",
										'input_value'  => $cart_item['quantity'],
										'max_value'    => $_product->get_max_purchase_quantity(),
										'min_value'    => '1',
										'product_name' => $_product->get_name(),
									), $_product, false );
								}

								if ( empty( $product_permalink ) ) :
									$output .= $thumbnail;
									$output .= '<div class="zt-mini-cart__info">';
										$output .= '<h4>' . $product_name . '</h4>';
										$output .= $product_price;
										$output .= apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
									$output .= '</div>';
								else :
									$output .= $thumbnail;
									$output .= '<div class="zt-mini-cart__info">';
										$output .= '<h4><a href="' . esc_url( $product_permalink ) . '">' . $product_name . '</a></h4>';
										$output .= $product_price;
										$output .= apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
									$output .= '</div>';
								endif;

								wc_get_formatted_cart_item_data( $cart_item );
								$output .= '</div>';
							endif;
						endforeach;
					} else {
						$output .= '<p>' . esc_html__('No products in the cart.', 'zt-ajax-mini-cart') . '</p>';
					}
				$output .= '</div>';

				$output .= '<div class="zt-mini-cart__footer">';
					$output .= '<div class="zt-mini-cart__subtotal">' . WC()->cart->get_cart_subtotal() . '</div>';
					$output .= '<a href="' . wc_get_cart_url() . '" class="zt-mini-cart__link">' . esc_html__('View Cart', 'zt-ajax-mini-cart') . '</a>';
					$output .= '<a href="#" class="zt-mini-cart__link zt-mini-cart__link--empty ' . esc_attr( $link_class ) . '">' . esc_html__('Empty Cart', 'zt-ajax-mini-cart') . '</a>';
				$output .= '</div>';
			$output .= '</div>';
		$output .= '</div>';

		$fragments['div.zt-mini-cart'] = $output;

		return $fragments;
	}

	/**
	 * Remove product in the mini cart.
	 */
	public function remove_product_in_mini_cart() {

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			if ( $cart_item['product_id'] == $_POST['product_id'] && $cart_item_key == $_POST['cart_item_key'] ) {
				WC()->cart->remove_cart_item( $cart_item_key );
			}
		}

		WC()->cart->calculate_totals();
		WC()->cart->maybe_set_cart_cookies();

		$link_class = WC()->cart->get_cart_contents_count() >= 1 ? 'js-empty-cart' : 'zt-mini-cart__link--disabled';

		// Fragments and mini cart are returned
		$data = array(
			'fragments' => array(
				'div.zt-mini-cart__list'      => $this->output_mini_cart_list(),
				'span.zt-mini-cart__count'    => '<span class="zt-mini-cart__count">' . WC()->cart->get_cart_contents_count() . '</span>',
				'div.zt-mini-cart__subtotal'  => '<div class="zt-mini-cart__subtotal">' . WC()->cart->get_cart_subtotal() . '</div>',
				'a.zt-mini-cart__link--empty' => '<a href="#" class="zt-mini-cart__link zt-mini-cart__link--empty ' . esc_attr( $link_class ) . '">' . esc_html__('Empty Cart', 'zt-ajax-mini-cart') . '</a>',
			),
			'cart_hash' => apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() )
		);

		wp_send_json( $data );

		wp_die();
	}

	/**
	 * Before quantity field.
	 */
	public function add_before_quantity_field() {
		$output = '<span class="quantity__minus" data-type="remove">' . esc_html__('-', 'zt-ajax-mini-cart') . '</span>';

		echo wp_kses_post( $output );
	}

	/**
	 * After quantity field.
	 */
	public function add_after_quantity_field() {
		$output = '<span class="quantity__plus" data-type="add">' . esc_html__('+', 'zt-ajax-mini-cart') . '</span>';

		echo wp_kses_post( $output );
	}

	public function output_mini_cart_list() {
		$output = '';

		$output .= '<div class="zt-mini-cart__list">';

		if ( ! WC()->cart->is_empty() ) {
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
				$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

				if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) :
					$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
					$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
					$product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );

					$output .= '<div class="zt-mini-cart__item ' . esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ) . '">';
					$output .= apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
						'<a href="%s" class="zt-mini-cart__remove-item" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s">&times;</a>',
						esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
						__( 'Remove this item', 'zt-ajax-mini-cart' ),
						esc_attr( $product_id ),
						esc_attr( $cart_item_key ),
						esc_attr( $_product->get_sku() )
					), $cart_item_key );

					if ( $_product->is_sold_individually() ) {
						$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
					} else {
						$product_quantity = $this->quantity_input( array(
							'input_name'   => "cart[{$cart_item_key}][qty]",
							'input_value'  => $cart_item['quantity'],
							'max_value'    => $_product->get_max_purchase_quantity(),
							'min_value'    => '1',
							'product_name' => $_product->get_name(),
						), $_product, false );
					}

					if ( empty( $product_permalink ) ) :
						$output .= $thumbnail;
						$output .= '<div class="zt-mini-cart__info">';
						$output .= '<h4>' . $product_name . '</h4>';
						$output .= $product_price;
						$output .= apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
						$output .= '</div>';
					else :
						$output .= $thumbnail;
						$output .= '<div class="zt-mini-cart__info">';
						$output .= '<h4><a href="' . esc_url( $product_permalink ) . '">' . $product_name . '</a></h4>';
						$output .= $product_price;
						$output .= apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
						$output .= '</div>';
					endif;

					wc_get_formatted_cart_item_data( $cart_item );
					apply_filters( 'woocommerce_widget_cart_item_quantity', '<span class="quantity">' . sprintf( '%s &times; %s', $cart_item['quantity'], $product_price ) . '</span>', $cart_item, $cart_item_key );
					$output .= '</div>';
				endif;
			endforeach;
		} else {
			$output .= '<p>' . esc_html__('No products in the cart.', 'zt-ajax-mini-cart') . '</p>';
		}

		$output .= '</div>';

		return $output;
	}

	/**
	 * Update mini cart.
	 */
	public function update_quantity_product() {
		// Set item key as the hash found in input.qty name
		$cart_item_key = sanitize_text_field( $_POST['item_hash'] );

		// Get the array of values owned by the product we're updating
		$threeball_product_values = WC()->cart->get_cart_item( $cart_item_key );

		// Get the quantity of the item in the cart
		$threeball_product_quantity = apply_filters( 'woocommerce_stock_amount_cart_item', apply_filters( 'woocommerce_stock_amount', preg_replace( "/[^0-9\.]/", '', filter_var( sanitize_text_field( $_POST['quantity'] ), FILTER_SANITIZE_NUMBER_INT ) ) ), $cart_item_key );

		// Update cart validation
		$passed_validation  = apply_filters( 'woocommerce_update_cart_validation', true, $cart_item_key, $threeball_product_values, $threeball_product_quantity );

		// Update the quantity of the item in the cart
		if ( $passed_validation ) {
			WC()->cart->set_quantity( $cart_item_key, $threeball_product_quantity, true );
		}

		$link_class = WC()->cart->get_cart_contents_count() >= 1 ? 'js-empty-cart' : 'zt-mini-cart__link--disabled';

		$data = array(
			'fragments' => array(
				'div.zt-mini-cart__list'      => $this->output_mini_cart_list(),
				'span.zt-mini-cart__count'    => '<span class="zt-mini-cart__count">' . WC()->cart->get_cart_contents_count() . '</span>',
				'div.zt-mini-cart__subtotal'  => '<div class="zt-mini-cart__subtotal">' . WC()->cart->get_cart_subtotal() . '</div>',
				'a.zt-mini-cart__link--empty' => '<a href="#" class="zt-mini-cart__link zt-mini-cart__link--empty ' . esc_attr( $link_class ) . '">' . esc_html__('Empty Cart', 'zt-ajax-mini-cart') . '</a>',
			),
			'cart_hash' => apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() )
		);

		wp_send_json( $data );

		wp_die();
	}

	public function quantity_input( $args = array(), $product = null, $echo = true ) {
		if ( is_null( $product ) ) {
			$product = $GLOBALS['product'];
		}

		$defaults = array(
			'input_id'     => uniqid( 'quantity_' ),
			'input_name'   => 'quantity',
			'input_value'  => '1',
			'classes'      => apply_filters( 'woocommerce_quantity_input_classes', array( 'input-text', 'qty', 'text' ), $product ),
			'max_value'    => apply_filters( 'woocommerce_quantity_input_max', -1, $product ),
			'min_value'    => apply_filters( 'woocommerce_quantity_input_min', 0, $product ),
			'step'         => apply_filters( 'woocommerce_quantity_input_step', 1, $product ),
			'pattern'      => apply_filters( 'woocommerce_quantity_input_pattern', has_filter( 'woocommerce_stock_amount', 'intval' ) ? '[0-9]*' : '' ),
			'inputmode'    => apply_filters( 'woocommerce_quantity_input_inputmode', has_filter( 'woocommerce_stock_amount', 'intval' ) ? 'numeric' : '' ),
			'product_name' => $product ? $product->get_title() : '',
		);

		$args = apply_filters( 'woocommerce_quantity_input_args', wp_parse_args( $args, $defaults ), $product );

		// Apply sanity to min/max args - min cannot be lower than 0.
		$args['min_value'] = max( $args['min_value'], 0 );
		$args['max_value'] = 0 < $args['max_value'] ? $args['max_value'] : '';

		// Max cannot be lower than min if defined.
		if ( '' !== $args['max_value'] && $args['max_value'] < $args['min_value'] ) {
			$args['max_value'] = $args['min_value'];
		}

		ob_start();

		if ( $args['max_value'] && $args['min_value'] === $args['max_value'] ) {
			?>
			<div class="quantity hidden">
				<input type="hidden" id="<?php echo esc_attr( $args['input_id'] ); ?>" class="qty" name="<?php echo esc_attr( $args['input_name'] ); ?>" value="<?php echo esc_attr( $args['min_value'] ); ?>" />
			</div>
			<?php
		} else {
			/* translators: %s: Quantity. */
			$label = ! empty( $args['product_name'] ) ? sprintf( esc_html__( '%s quantity', 'woocommerce' ), wp_strip_all_tags( $args['product_name'] ) ) : esc_html__( 'Quantity', 'woocommerce' );
			?>
			<div class="quantity">
				<?php do_action( 'woocommerce_before_quantity_input_field' ); ?>
				<label class="screen-reader-text" for="<?php echo esc_attr( $args['input_id'] ); ?>"><?php echo esc_attr( $args['label'] ); ?></label>
				<input
					type="number"
					id="<?php echo esc_attr( $args['input_id'] ); ?>"
					class="<?php echo esc_attr( join( ' ', (array) $args['classes'] ) ); ?>"
					step="<?php echo esc_attr( $args['step'] ); ?>"
					min="<?php echo esc_attr( $args['min_value'] ); ?>"
					max="<?php echo esc_attr( 0 < $args['max_value'] ? $args['max_value'] : '' ); ?>"
					name="<?php echo esc_attr( $args['input_name'] ); ?>"
					value="<?php echo esc_attr( $args['input_value'] ); ?>"
					title="<?php echo esc_attr_x( 'Qty', 'Product quantity input tooltip', 'woocommerce' ); ?>"
					size="4"
					inputmode="<?php echo esc_attr( $args['inputmode'] ); ?>" />
				<?php do_action( 'woocommerce_after_quantity_input_field' ); ?>
			</div>
			<?php
		}

		if ( $echo ) {
			echo ob_get_clean(); // WPCS: XSS ok.
		} else {
			return ob_get_clean();
		}
	}
}

new ZT_Ajax_Mini_Cart_Front_End();
