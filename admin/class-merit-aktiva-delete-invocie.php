<?php
class Delete_Merit_Invoices {
    private $api_url = 'https://aktiva.merit.ee/api/v1/deleteinvoice';


    public function __construct() {

        add_action('current_screen', array($this, 'filter_trash_invoices') );
        add_action('admin_init', array($this, 'filter_trash_invoices'));
        add_action('admin_init', array($this, 'delete_trash_invoices'));
        add_action('plugins_loaded', array($this, 'init'));
        $this->api_key = get_option('apikey_text_field');

    }

    public function init() {
        if (class_exists('WooCommerce')) {
            add_action('admin_init', array($this, 'filter_trash_invoices'));
        }
    }

    public function sample_admin_notice__success($text) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e( $text, 'sample-text-domain' ); ?></p>
        </div>
        <?php


    }

    public function filter_trash_invoices()
    {
        if ( !function_exists( 'get_current_screen' ) ) {
            require_once ABSPATH . '/wp-admin/includes/screen.php';
        }

        // Set up the query arguments
        $args = array(
            'post_type'      => 'shop_order',
            'post_status'    => 'trash',
            'posts_per_page' => -1, // Retrieve all trashed orders
        );

        $screen = get_current_screen();
        $invoicenr_array = [];
        //if (isset($screen->base)) {
            //if ($screen->base == "edit") {
                // Create a new WP_Query instance
                $trashed_orders_query = new WP_Query( $args );

                $orders = $trashed_orders_query->posts;

                $MeritGetAllInvoices = new Get_All_Merit_Invoices();



                print_r('<pre style="margin-left:40%" >');
                //print_r($screen);
                print_r('</pre>');

                if (!empty($orders)) {
                    foreach ($orders as $orderID => $order) {

                        $meritAPI_request = $MeritGetAllInvoices->make_api_request();

                        if (isset($order) && isset($meritAPI_request['invoiceid'])) {
                            if (is_array($MeritGetAllInvoices->make_api_request())) {
                                if (in_array($order->ID, array_keys($meritAPI_request['invoiceid']))) {

                                    array_push($invoicenr_array, $meritAPI_request['invoiceid'][$order->ID]);

                                }
                            }
                        }

                    }
                }
            //}
        //}
        return $invoicenr_array;

    }


    public function delete_trash_invoices(){

        $timestamp = gmdate('YmdHis');
        $signature = hash_hmac('sha256', $timestamp, $this->api_key);

        $invoiceSIHID_array = $this->filter_trash_invoices();

        foreach ($invoiceSIHID_array as $invoiceSIHID ) {

            // Construct the payload
            $payload = array(
                'Id' => $invoiceSIHID,
            );




            $request_url = $this->api_url . '?ApiId=' . $this->api_key . '&timestamp=' . $timestamp . '&signature=' . $signature;

            $request_args = array(
                'body' => json_encode($payload),
                'headers' => array('Content-Type' => 'application/json'),
                'method' => 'POST',
            );
            //if(!empty($invoiceSIHID_array)){
                // Make the POST request
                $response = wp_remote_request($request_url, $request_args);

            //}

            if (is_wp_error($response)) {
                // Handle error
                echo 'Error: ' . $response->get_error_message();
            } else {
                $body = wp_remote_retrieve_body($response);

                if(! empty($body)) {
                    $this->sample_admin_notice__success($body);
                }

            }
        }

    }

}



new Delete_Merit_Invoices();

