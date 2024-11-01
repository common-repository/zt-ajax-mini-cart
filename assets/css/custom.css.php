<?php
header("Content-type: text/css; charset: UTF-8");

$zt_mini_cart_options = get_option( 'zt_mini_cart_options' );

// Add inline styles
$styles = '';

// Change button position
if ( $zt_mini_cart_options['button_position'] === 'top_left' ) {
	$styles .= '.zt-mini-cart__btn { top: 15px; left: 15px; }';
	$styles .= '.admin-bar .zt-mini-cart__btn { top: 47px; }';
}

if ( $zt_mini_cart_options['button_position'] === 'top_right' ) {
	$styles .= '.zt-mini-cart__btn { top: 15px; right: 15px; }';
	$styles .= '.admin-bar .zt-mini-cart__btn { top: 47px; }';
}

if ( $zt_mini_cart_options['button_position'] === 'bottom_left' ) {
	$styles .= '.zt-mini-cart__btn { bottom: 15px; left: 15px; top: auto; }';
}

if ( $zt_mini_cart_options['button_position'] === 'bottom_right' ) {
	$styles .= '.zt-mini-cart__btn { bottom: 15px; right: 15px; top: auto; }';
}

if ( $zt_mini_cart_options['button_position'] === 'left_center' ) {
	$styles .= '.zt-mini-cart__btn { top: 50%; left: 15px; transform: translateY(-50%); }';
}

if ( $zt_mini_cart_options['button_position'] === 'right_center' ) {
	$styles .= '.zt-mini-cart__btn { top: 50%; right: 15px; transform: translateY(-50%); }';
}

// Change general color
if ( ! empty( $zt_mini_cart_options['general_color'] ) ) {
	$styles .= '.zt-mini-cart__count, .zt-mini-cart__close i, .zt-mini-cart__close i::after, .zt-mini-cart__link, .zt-mini-cart__remove-item {background-color: ' . $zt_mini_cart_options['general_color'] . ';}';
	$styles .= '.zt-mini-cart__info a, .zt-mini-cart__info a:focus, .zt-mini-cart__info a:visited, .zt-mini-cart__link:hover {color: ' . $zt_mini_cart_options['general_color'] . ';}';
	$styles .= '.zt-mini-cart__link {border-color: ' . $zt_mini_cart_options['general_color'] . ';}';
}

echo $styles;
