;(function($, window, document, undefined) {
    "use strict";

    var MINI_CART_OBJECT = {};

    MINI_CART_OBJECT.init = function () {
        this.show_mini_cart();
        this.close_mini_cart();
        this.empty_mini_cart();
        this.remove_product_in_mini_cart();
        this.show_info_after_product_added();
        this.quantity_input();
    };

    // Show mini cart
    MINI_CART_OBJECT.show_mini_cart = function() {
        $('body').on('click', '.zt-mini-cart__btn', function () {
            document.querySelector('.zt-mini-cart').classList.add('zt-mini-cart--open');
            document.body.style.overflow = 'hidden';
        });
    };

    // Close mini cart
    MINI_CART_OBJECT.close_mini_cart = function() {
        $('body').on('click', '.zt-mini-cart__close', function () {
            document.querySelector('.zt-mini-cart').classList.remove('zt-mini-cart--open');
            document.body.style.overflow = '';
        });
    };

    // Show info after add product to cart
    MINI_CART_OBJECT.show_info_after_product_added = function() {
        $(document).ajaxSuccess(function(event, xhr, settings) {
            if ( settings.url.indexOf('?wc-ajax=add_to_cart') !== -1 ) {

                document.querySelector('body').innerHTML += '<div class="zt-mini-cart__message"><p>Product has been added to your cart.</p></div>';

                setTimeout(function () {
                    if ( document.querySelector('.zt-mini-cart__message') ) {
                        document.querySelector('.zt-mini-cart__message').classList.add('zt-mini-cart__message--active');
                    }
                }, 500);

                setTimeout(function () {
                    if ( document.querySelector('.zt-mini-cart__message') ) {
                        document.querySelector('.zt-mini-cart__message').remove();
                    }
                }, 3000);
            }
        });
    };

    // Empty Cart
    MINI_CART_OBJECT.empty_mini_cart = function() {
        $('body').on('click', '.js-empty-cart', function (event) {
            event.preventDefault();

            $.ajax({
                type: 'POST',
                url: mini_cart_data.ajax_url,
                data: {
                    'action': 'empty_mini_cart',
                    'empty-cart': true
                },
                beforeSend: function () {
                    var loader_img = '<div class="zt-mini-cart__loader"><img src="' + mini_cart_data.site_url + '/assets/img/ajax-loader.gif" /></div>';
                    document.querySelector('.zt-mini-cart__content').innerHTML += loader_img;
                },
                success: function (response) {
                    var btn = document.querySelector('.js-empty-cart');

                    btn.classList.add('zt-mini-cart__link--disabled');
                    btn.classList.remove('js-empty-cart');

                    response = JSON.parse( response );

                    document.querySelector('.zt-mini-cart__loader').remove();
                    document.querySelector('.zt-mini-cart__count').innerHTML = '0';
                    document.querySelector('.zt-mini-cart__list').innerHTML = '<p>' + response.message + '</p>';
                    document.querySelector('.zt-mini-cart__subtotal').innerHTML = '<p>' + response.subtotal + '</p>';
                }
            });

            return false;
        });
    };

    // Remove product from cart
    MINI_CART_OBJECT.remove_product_in_mini_cart = function() {
        $('body').on('click', '.zt-mini-cart__remove-item', function (event) {
            event.preventDefault();

            var product_id    = this.getAttribute('data-product_id'),
                cart_item_key = this.getAttribute('data-cart_item_key');

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: mini_cart_data.ajax_url,
                data: {
                    'action': 'remove_product_in_mini_cart',
                    'product_id': product_id,
                    'cart_item_key': cart_item_key
                },
                beforeSend: function () {
                    var loader_img = '<div class="zt-mini-cart__loader"><img src="' + mini_cart_data.site_url + '/assets/img/ajax-loader.gif" /></div>';
                    document.querySelector('.zt-mini-cart__content').innerHTML += loader_img;
                },
                success: function (response) {
                    if ( ! response || response.error )
                        return;

                    document.querySelector('.zt-mini-cart__loader').remove();

                    $.each(response.fragments, function (key, value) {
                        $(key).replaceWith(value);
                    });
                }
            });

            return false;
        });
    };

    // Quantity input
    MINI_CART_OBJECT.quantity_input = function() {
        document.querySelector('body').addEventListener('click', function (e) {
            if ( ! e.target.hasAttribute('data-type') ) return false;

            e.preventDefault();

            var input = e.target.parentElement.querySelector('input[type="number"]'),
                type = e.target.getAttribute('data-type');

            if ( type === 'add' && input.value >= 1 ) {
                input.value++;
            } else {
                if ( parseInt(input.value) === 1 ) return false;

                input.value--;
            }

            if ( $(e.target).closest('.zt-mini-cart__list').length ) {
                MINI_CART_OBJECT.update_mini_cart(e.target, input.value);
            }
        });
    };

    // Update mini cart
    MINI_CART_OBJECT.update_mini_cart = function(btn, quantity) {
        var item_hash = $(btn).parent().find('input[type="number"]').attr('name').replace(/cart\[([\w]+)\]\[qty\]/g, "$1");

        $.ajax({
            url: mini_cart_data.ajax_url,
            type: 'POST',
            data: {
                'action': 'update_quantity_product',
                'item_hash': item_hash,
                'quantity': quantity
            },
            beforeSend: function () {
                var loader_img = '<div class="zt-mini-cart__loader"><img src="' + mini_cart_data.site_url + '/assets/img/ajax-loader.gif" /></div>';
                document.querySelector('.zt-mini-cart__content').innerHTML += loader_img;
            },
            success: function (response) {
                if ( ! response || response.error )
                    return;

                document.querySelector('.zt-mini-cart__loader').remove();

                $.each(response.fragments, function (key, value) {
                    $(key).replaceWith(value);
                });
            }
        });

        return false;
    };

    document.addEventListener('DOMContentLoaded', function() {
        MINI_CART_OBJECT.init();
    });

})(jQuery, window, document);



