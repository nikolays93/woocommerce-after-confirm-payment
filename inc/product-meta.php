<?php
/**
 * Добавляем форму перед кнопкой купить
 */
function add_gift_wrap_field() {
    echo '<table class="variations" cellspacing="0">
            <tbody>
                <tr>
                    <td class="label"><label>Выслать цветопробу:</label></td>
                    <td class="value">
                        <label><input type="checkbox" name="option_gift_wrap" value="YES" /></label>                        
                    </td>
                </tr>                             
            </tbody>
        </table>';
}
add_action( 'woocommerce_before_add_to_cart_button', 'add_gift_wrap_field' );

/**
 * Добавляем товару доп. данные
 */
function save_gift_wrap_fee( $cart_item_data, $product_id ) {
    if( isset( $_POST['option_gift_wrap'] ) && $_POST['option_gift_wrap'] === 'YES' ) {
        $cart_item_data[ "gift_wrap_fee" ] = "YES";     
    }
    return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'save_gift_wrap_fee', 99, 2 );

/**
 * Показать добавленные параметры
 */
add_filter( 'woocommerce_get_item_data', 'render_meta_on_cart_and_checkout', 99, 2 );
function render_meta_on_cart_and_checkout( $cart_data, $cart_item = null ) {
    $meta_items = array();
    /* Woo 2.4.2 updates */
    if( !empty( $cart_data ) ) {
        $meta_items = $cart_data;
    }
    if( isset( $cart_item["gift_wrap_fee"] ) ) {
        $meta_items[] = array( "name" => "Выслать цветопробу", "value" => "Да" );
    }
    return $meta_items;
}

add_filter( 'woocommerce_available_payment_gateways', 'disable_after_order_gateways' );
function disable_after_order_gateways( $gateways ){
    if( str_match_cart_item_data('Выслать цветопробу') )
        unset($gateways['cod']);
    else
        unset($gateways['after_confirm']);

    return $gateways;
}