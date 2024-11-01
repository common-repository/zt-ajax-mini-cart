<?php

class ZT_Ajax_Mini_Cart_Admin {
	public function __construct() {
		$this->hooks();
	}

	public function hooks() {
		// Create Admin Setting Menu
		add_action( 'admin_menu', array( $this, 'admin_page' ) );
		add_action( 'admin_init', array( $this, 'settings' ) );
	}

	public function admin_page() {
		add_submenu_page(
			'tools.php',
			esc_html__( 'Ajax Mini Cart', 'zt-ajax-mini-cart' ),
			esc_html__( 'Ajax Mini Cart', 'zt-ajax-mini-cart' ),
			'manage_options',
			'ajax-mini-cart',
			array( $this, 'setting_form' )
		);
	}

	public function setting_form() {
		?>
		<div class="wrap">
			<h2><?php echo get_admin_page_title(); ?></h2>

            <p class="help"><?php esc_html_e('Use these options to change the functionality and styling of mini cart.', 'zt-ajax-mini-cart'); ?></p>

			<form action="<?php echo admin_url( 'options.php' ); ?>" method="post">
				<?php
					settings_fields( 'zt_mini_cart_options_page' );
					do_settings_sections( 'zt_mini_cart_options_section' );
					submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public function settings() {
		register_setting( 'zt_mini_cart_options_page', 'zt_mini_cart_options' );
		add_settings_section( 'general', '', '', 'zt_mini_cart_options_section' );

		// Add settings field
		$array_fields = array(
			array(
				'id'    => 'button_position',
				'title' => esc_html__('Button Position', 'zt-ajax-mini-cart'),
				'args'  => array( 'type_field' => 'select', 'option_name' => 'button_position', 'value' => array( 'right_center' => 'Right Center', 'left_center' => 'Left Center', 'top_left' => 'Top Left', 'top_right' => 'Top Right', 'bottom_left' => 'Bottom Left', 'bottom_right' => 'Bottom Right' ) )
			),
			array(
				'id'    => 'general_color',
				'title' => esc_html__('General Color', 'zt-ajax-mini-cart'),
				'args'  => array( 'type_field' => 'color', 'option_name' => 'general_color', 'value' => '' )
			),
		);

		foreach ( $array_fields as $item ) {
			$item['args']['page'] = 'zt';
			$this->add_setting_field( $item['id'], $item['title'], array( $this, 'setting_field_callback' ), 'zt_mini_cart_options_section', 'general', $item['args'] );
		}
	}

	public function add_setting_field( $id, $title, $callback, $page, $section, $args ) {
		add_settings_field( $id, $title, $callback, $page, $section, $args );
	}

	public function setting_field_callback( $args ) {
		$val = get_option( $args['page'] . '_mini_cart_options' );
		$val = ! empty( $val[ $args['option_name'] ] ) ? $val[ $args['option_name'] ] : null;

		if ( $args['type_field'] == 'select' ) : ?>
			<select name="<?php echo esc_attr( $args['page'] . '_mini_cart_options[' . $args['option_name'] . ']' ); ?>"
			        id="<?php echo esc_attr( $args['option_name'] ); ?>">
				<?php foreach ( $args['value'] as $key => $item ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $val, $key ); ?>><?php echo esc_html( $item ); ?></option>
				<?php endforeach; ?>
			</select>
		<?php elseif ( $args['type_field'] == 'checkbox' ) : ?>
			<input type="checkbox" id="<?php echo esc_attr( $args['option_name'] ); ?>"
			       name="<?php echo esc_attr( $args['page'] . '_mini_cart_options[' . $args['option_name'] . ']' ); ?>"
			       value="<?php echo esc_attr( $args['value'] ); ?>" <?php checked( $val, $args['value'] ); ?>>
		<?php elseif ( $args['type_field'] == 'text' || $args['type_field'] == 'color' ) : ?>
			<input type="text" id="<?php echo esc_attr( $args['option_name'] ); ?>"
			       name="<?php echo esc_attr( $args['page'] . '_mini_cart_options[' . $args['option_name'] . ']' ); ?>"
			       value="<?php echo esc_attr( $val ); ?>">
		<?php endif;
	}
}

new ZT_Ajax_Mini_Cart_Admin();