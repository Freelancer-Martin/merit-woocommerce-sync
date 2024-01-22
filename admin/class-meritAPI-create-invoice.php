<?php
class Merit_AktivaAPI_Create_Invoice {
    private $api_url = 'https://aktiva.merit.ee/api/v2/sendinvoice';

    public function __construct()
    {
        // Add an action to trigger your method when necessary
        add_action('init', array($this, 'create_invoice'));
        $this->api_key = get_option('apikey_text_field');
        $this->tax_field = get_option('tax_select_field');

    }

    public function create_invoice_items_array($order)
    {
        // Getting an instance of the WC_Order object from a defined ORDER ID
        $order = wc_get_order( $order->id );
        $payload_arrays = [];

        foreach ($order->get_items() as $item_id => $item ) {

            // Get an instance of corresponding the WC_Product object
            $product = $item->get_product();

            // Displaying this data (to check)
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

        return $payload_arrays;
    }

    public function sample_admin_notice__success($text) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e( $text, 'sample-text-domain' ); ?></p>
        </div>
        <?php


    }



    public function create_invoice($order) {
        $timestamp = gmdate('YmdHis');
        $signature = hash_hmac('sha256', $timestamp, $this->api_key);

        if(!empty($order)) {
            $json_payload = array(
                "Customer" => array(
                    "Name" => $order->get_billing_company(),
                    "RegNo" => get_post_meta($order->id, 'Register_code', true),
                    "NotTDCustomer" => false,
                    "VatRegNo" => get_post_meta($order->id, 'Register_code', true),
                    "CurrencyCode" => $order->get_currency(),
                    "PaymentDeadLine" => 7,
                    "OverDueCharge" => 0,
                    "RefNoBase" => 1,
                    "Address" => $order->get_billing_address_1() . $order->get_billing_address_2(),
                    "CountryCode" => $order->get_billing_country(),
                    "County" => $order->get_shipping_state(),
                    "City" => $order->get_billing_city(),
                    "PostalCode" => $order->get_shipping_postcode(),
                    "PhoneNo" => $order->get_billing_phone(),
                    "PhoneNo2" => "",
                    "HomePage" => "",
                    "Email" => $order->get_billing_email()
                ),
                "DocDate" => $order->get_date_created()->date("YmdGis"),
                "DueDate" => date("YmdGis", mktime(date("G"), date("i"), date("s"), date("m"), date("d") + 14, date("Y"))),
                "InvoiceNo" => $order->id,
                "RefNo" => "0000",
                "DepartmentCode" => "",
                "ProjectCode" => "",
                "InvoiceRow" => $this->create_invoice_items_array($order),
                "TotalAmount" => $order->get_total(),
                "RoundingAmount" => 0,
                "TaxAmount" => array(
                    array(
                        "TaxId" => $this->tax_field,
                        "Amount" => 0
                    )
                ),
                "HComment" => "",
                "FComment" => ""
            );

            $request_url = $this->api_url . '?ApiId=' . $this->api_key . '&timestamp=' . $timestamp . '&signature=' . $signature;

            $ch = curl_init($request_url);

            // Set cURL options
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json_payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);


            // Execute cURL request
            $response = curl_exec($ch);

            // Check for errors
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
                print_r($error_msg);
                // Handle error here
            }

            // Close cURL session
            curl_close($ch);

            if(! empty($order->id)) {
                $this->sample_admin_notice__success('Invoice number ' . $order->id . ' has been added to Merit aktiva server');
            }
        }

    }
}

