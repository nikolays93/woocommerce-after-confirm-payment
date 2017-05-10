<?php

/**
 * Plugin Name: After Confirm Payment GateWay
 * Plugin URI: https://github.com/nikolays93/woocommerce-after-confirm-payment
 * Description: Pay after agreement
 * Version: 0.1
 * Author: NikolayS93
 * Author URI: https://vk.com/nikolays_93
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

function str_match_cart_item_data( $str ){
    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
        $attr = WC()->cart->get_item_data($cart_item, true);
        if( $attr && preg_match("/".$str."/i", $attr) )
            return true;
    }
    return false;
}

require_once __DIR__ . "/inc/preorder-product-meta.php";
require_once __DIR__ . "/inc/custom-gateway.php";