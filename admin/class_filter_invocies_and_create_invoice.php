<?php
class Merit_AktivaAPI_Filter_Invocies {
    private $api_url = 'https://aktiva.merit.ee/api/v2/createinvoice';
    private $api_key = '55d7f89a-67bb-4938-aa53-e484e10f2206';

    public function __construct() {
        // Add an action to trigger your method when necessary
        //add_action('init', array($this, 'create_invoice'));
        add_action('admin_init', array($this, 'someMethod'));
        //add_action('woocommerce_after_register_post_type', array($this,'someMethod'));
        add_action('plugins_loaded', array($this, 'init'));
        //$this->someMethod();
    }

    public function init() {
        if (class_exists('WooCommerce')) {
            add_action('admin_init', array($this, 'someMethod'));
        }
    }

    public function someMethod() {
        // Get all orders using WC_Order_Query
        $args = array(
            'post_type' => 'shop_order',
            //'post_status' => '', // Change this status as needed
            'posts_per_page' => -1, // Retrieve all orders
        );

        $order_query = new WC_Order_Query($args);
        $orders = $order_query->get_orders();

        // Create an instance of the Get_All_Woocommerce_Invocies class
        $ClassCreateInvoice = new Merit_AktivaAPI_Create_Invoice();
        $ordersClass = new Get_All_Woocommerce_Invocies();

        foreach ($orders as $orderID => $order)
        {


            $filtered_invoice_array = $ordersClass->my_custom_plugin_get_orders($order);
            //$ClassCreateInvoice->create_invoice($filtered_invoice_array);
            print_r('PAYLOAD-------------------------------------------------');
            print_r('<pre style="margin-left: 40%;">');
            //print_r($filtered_invoice_array);
            print_r('</pre>');
        }

        //return $ordersArray
        //print_r($ordersArray);

        // Do something with $result
        // ...
    }



}

// Instantiate the class
new Merit_AktivaAPI_Filter_Invocies();
