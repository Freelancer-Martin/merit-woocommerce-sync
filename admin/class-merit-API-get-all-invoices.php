<?php
class Get_All_Merit_Invoices {
    private $api_url = 'https://aktiva.merit.ee/api/v2/getinvoices';
    private $api_key = '55d7f89a-67bb-4938-aa53-e484e10f2206';

    public function __construct() {
        // Add an action to trigger your method when necessary
        add_action('init', array($this, 'make_api_request'));
    }

    public function make_api_request() {
        $timestamp = gmdate('YmdHis');
        $signature = hash_hmac('sha256', $timestamp, $this->api_key);

        $payload = array(
            'Periodstart' => 20230720,
            'PeriodEnd' => 20230928,
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
            // $body contains the response data
            //echo $body;
            //print_r($body);
        }
    }
}

// Instantiate the class
//new Get_All_Merit_Invoices();




