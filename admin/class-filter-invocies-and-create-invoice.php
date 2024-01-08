<?php
class Merit_AktivaAPI_Filter_Invocies {

    public function __construct() {

        add_action('admin_init', array($this, 'Filter_Woocommerce_Meritaktiva_Invoices'));
        add_action('plugins_loaded', array($this, 'init'));
        add_action( 'current_screen', array($this, 'Filter_Woocommerce_Meritaktiva_Invoices') );
        $this->api_key = get_option('apikey_text_field');
        $this->payment_status = get_option('payment_status_select_field');
    }

    public function init() {
        if (class_exists('WooCommerce')) {
            add_action('admin_init', array($this, 'Filter_Woocommerce_Meritaktiva_Invoices'));
        }
    }

    public function Filter_Woocommerce_Meritaktiva_Invoices() {
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
        $MeritGetAllInvoices = new Get_All_Merit_Invoices();
        $screen = get_current_screen();


        if (isset($screen->base)) {
            if ($screen->base == "dashboard") {
                foreach ($orders as $orderID => $order) {
                    $filtered_invoice_array = wc_get_order($order->id);
                    $meritAPI_request = $MeritGetAllInvoices->make_api_request();

                        if (is_array($MeritGetAllInvoices->make_api_request())) {
                            if (!in_array($order->id, $meritAPI_request['invoicenr']) || !in_array($order->get_total(), $meritAPI_request['totalamount'])) {
                                if ($this->payment_status == $order->get_status() || $this->payment_status == 'all'){
                                    $ClassCreateInvoice->create_invoice($filtered_invoice_array);
                                }
                             }
                         }
                    }
                 }

             }
    }




}

// Instantiate the class
new Merit_AktivaAPI_Filter_Invocies();
