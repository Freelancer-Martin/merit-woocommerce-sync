<?php
class Merit_AktivaAPI_Create_Invoice {
    private $api_url = 'https://aktiva.merit.ee/api/v2/sendinvoice';
    private $api_key = '55d7f89a-67bb-4938-aa53-e484e10f2206';

    public function __construct()
    {
        // Add an action to trigger your method when necessary
        add_action('init', array($this, 'create_invoice'));
        add_action( 'admin_notices', array($this, 'sample_admin_notice__success' ));
    }

    public function create_invoice_items_array($order)
    {
        // Getting an instance of the WC_Order object from a defined ORDER ID
        $order = wc_get_order( $order['get_id'] );
        $payload_arrays = [];
        //print_r('Products==============================');
        //print_r($order->get_items());
        // Iterating through each "line" items in the order
        foreach ($order->get_items() as $item_id => $item ) {

            // Get an instance of corresponding the WC_Product object
            $product        = $item->get_product();

            // Displaying this data (to check)
            //echo 'Product name: '.$product_name.' | Quantity: '.$item_quantity.' | Item total: '. number_format( $item_total, 2 );
            $payload_items_array = array(
                "Item" => array(
                    "Code" => $item->get_id(),
                    "Description" => $item->get_name(),
                    "Type" => 3, // needs UI FOR THAT
                    "UOMName" => "kg"
                ),
                "Quantity" => $item->get_quantity(),
                "Price" => $product->get_price(),
                "DiscountPct" => 0,
                "DiscountAmount" => 0,
                "TaxId" => "7e170b45-fe96-4048-b824-39733c33e734", // Needs UI setting for that
                "LocationCode" => "1" // Needs maybe UI for that
            );

            array_push($payload_arrays, $payload_items_array);


        }
        //print_r('<pre style="margin-left: 40%;">');
        //print_r($product->get_attributes() );
        //print_r($payload_arrays);
        //print_r('</pre>');
        return $payload_arrays;
    }

    public function sample_admin_notice__success($body) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e( 'Done!' . $body, 'sample-text-domain' ); ?></p>
        </div>
        <?php


    }



    public function create_invoice($order) {
        $timestamp = gmdate('YmdHis');
        $signature = hash_hmac('sha256', $timestamp, $this->api_key);
        //$date_invoice_created = $order['get_date_created'];
        //print_r('<pre style="margin-left: 40%;">');
        //print_r($order);
        //print_r('</pre>');

        //$this->create_invoice_items_array($order);

        if(!empty($order)) {
            $json_payload = array(
                "Customer" => array(
                    "Name" => $order['get_billing_company'],
                    "RegNo" => "1122334455",
                    "NotTDCustomer" => false,
                    "VatRegNo" => "11223344",
                    "CurrencyCode" => $order['get_currency'],
                    "PaymentDeadLine" => 7,
                    "OverDueCharge" => 0,
                    "RefNoBase" => 1,
                    "Address" => $order['get_billing_address_1'] . $order['get_billing_address_2'],
                    "CountryCode" => $order['get_billing_country'],
                    "County" => $order['get_shipping_state'],
                    "City" => $order['get_billing_city'],
                    "PostalCode" => $order['get_shipping_postcode'],
                    "PhoneNo" => $order['get_billing_phone'],
                    "PhoneNo2" => "",
                    "HomePage" => "",
                    "Email" => $order['get_billing_email']
                ),
                "DocDate" => $order['get_date_created']->date("YmdGis"),
                "DueDate" => date("YmdGis", mktime(date("G"), date("i"), date("s"), date("m"), date("d") + 14, date("Y"))),
                "InvoiceNo" => $order['get_id'],
                "RefNo" => "1232",
                "DepartmentCode" => "",
                "ProjectCode" => "",
                "InvoiceRow" => $this->create_invoice_items_array($order),
                "TotalAmount" => $order['get_total'],
                "RoundingAmount" => 0,
                "TaxAmount" => array(
                    array(
                        "TaxId" => "7e170b45-fe96-4048-b824-39733c33e734",
                        "Amount" => 0
                    )
                ),
                "HComment" => "",
                "FComment" => ""
            );


            $request_url = $this->api_url . '?ApiId=' . $this->api_key . '&timestamp=' . $timestamp . '&signature=' . $signature;

            $request_args = array(
                'body' => json_encode($json_payload),
                'headers' => array('Content-Type' => 'application/json'),
                'method' => 'POST',
            );


            // Make the POST request
            $response = wp_remote_request($request_url, $request_args);

            if (is_wp_error($response)) {
                // Handle error
                echo 'Error: ' . $response->get_error_message();
            } else {
                $body = wp_remote_retrieve_body($response);

                // Handle the API response here
                // $body contains the response data
                echo $body;

                $this->sample_admin_notice__success($body);
            }

        }

    }
}

// Instantiate the class
//new Merit_AktivaAPI_Create_Invoice();
