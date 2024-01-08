<?php
add_filter('woocommerce_checkout_fields', 'custom_woocommerce_billing_fields');

function custom_woocommerce_billing_fields($fields)
{
    $fields['billing']['Register_code'] = array(
        'label' => __('Company Register code', 'woocommerce'), // Add custom field label
        'placeholder' => _x('12345678', 'placeholder', 'woocommerce'), // Add custom field placeholder
        'required' => true, // if field is required or not
        'clear' => false, // add clear or not
        'type' => 'text', // add field type
        'class' => array('my-css')   // add class name
    );

    return $fields;
}

add_action( 'woocommerce_checkout_process', 'merit_aktiva_validate_new_checkout_field' );

function merit_aktiva_validate_new_checkout_field() {
    if ( ! $_POST['Register_code'] ) {
        wc_add_notice( 'Please enter Company Register code', 'error' );
    }
}

add_action( 'woocommerce_checkout_update_order_meta', 'merit_aktiva_save_new_checkout_field' );

function merit_aktiva_save_new_checkout_field( $order_id ) {
    if ( $_POST['Register_code'] ) update_post_meta( $order_id, 'Register_code', esc_attr( $_POST['Register_code'] ) );
}