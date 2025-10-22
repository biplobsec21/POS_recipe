<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class License extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Subscription_model');
        // $this->load->model('License'); // Load the License model

    }

    public function renew() {
        $this->load->view('license_renew');
    }
    public function submit_transaction() {
        
        // Get the transaction ID from the POST request
        $trx_id = $this->input->post('trxId');
        // // Validate the transaction ID length
        // // die(($trx_id));
        // echo strlen($trx_id) ;
        // die();

        if (strlen($trx_id) === 10) {
            // Store the transaction ID in the subscription table
            $data = [
                'transaction_id' => $trx_id,
                'company_name' => base_url(), // or get dynamically
                'subscription_status' => 'active',      // set appropriate status
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->Subscription_model->insert_subscription($data);
            $this->Subscription_model->update_license_status();

            //  // Use CI instance to load and call the License model
            // $CI =& get_instance();
            // $CI->load->model('License');
            // $CI->License->update_license_status();

            // Load the thank-you view
            // dd("here");
            // Load the thank-you view
            $this->load->view('thanks_image'); // Ensure this view exists in application/views
        } else {
            // Redirect back with an error message if the ID is invalid
            redirect('license/renew');
        }
    }
    public function thanks_image(){
        $this->load->view('thanks_image'); // Ensure this view exists in application/views

    }
}
