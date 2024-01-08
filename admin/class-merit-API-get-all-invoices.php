<?php
class Get_All_Merit_Invoices {
    private $api_url = 'https://aktiva.merit.ee/api/v2/getinvoices';


    public function __construct() {
        // Add an action to trigger your method when necessary
        add_action('init', array($this, 'make_api_request'));
        $this->api_key = get_option('apikey_text_field');
    }

    public function make_api_request() {
        $timestamp = gmdate('YmdHis');
        $signature = hash_hmac('sha256', $timestamp, $this->api_key);

        // Get today's date
        $today = new DateTime();

        // Calculate three months ago
        $threeMonthsAgo = (new DateTime())->modify('-3 months');

        // Format dates for the API payload
        $periodStart = $threeMonthsAgo->format('Ymd');
        $periodEnd = $today->format('Ymd');

        // Construct the payload
        $payload = array(
            'Periodstart' => intval($periodStart), // Convert to integer if needed
            'PeriodEnd' => intval($periodEnd), // Convert to integer if needed
            'UnPaid' => true,
        );

        $request_url = $this->api_url . '?ApiId=' . $this->api_key . '&timestamp=' . $timestamp . '&signature=' . $signature;

        $request_args = array(
            'body' => json_encode($payload),
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

            $decode_json = json_decode($body);
            $InvoiceNumberArray = [];
            $TotalAmountArray = [];
            foreach ( $decode_json as $json_array_nr => $json_array)
            {
                array_push($InvoiceNumberArray,$json_array->InvoiceNo);
                array_push($TotalAmountArray,$json_array->TotalAmount);

            }

            return array('invoicenr' => $InvoiceNumberArray, 'totalamount' => $TotalAmountArray);
        }
    }
}





