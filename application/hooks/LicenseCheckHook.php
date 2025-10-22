<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class LicenseCheckHook {

    public function validate_license() {
        $CI =& get_instance();

        // Get the current controller and method names
        $class = $CI->router->fetch_class();
        $method = $CI->router->fetch_method();

        // Skip license validation for specific methods
        if ($class == 'License' && ($method == 'renew' || $method == 'submit_transaction')) {
            return;
        }

        // Load the License model if it's not already loaded
        $CI->load->model('License');

        // Perform the license check as usual
        if (!$CI->License->check_license()) {
            // Redirect to license renew page if the license is invalid
            redirect(base_url('License/renew'));
            exit;
        }
    }

    public function submit_transaction($trx_id) {
        $CI =& get_instance();

        // Validate the transaction ID
        if (strlen($trx_id) === 10) {
            // Prepare the data for insertion
            $data = [
                'transaction_id' => $trx_id,
                'company_name' => 'Adoral lab',         // Set dynamically if needed
                'subscription_status' => 'active',      // Set appropriate status
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Load the Subscription model
            $CI->load->model('Subscription_model');
            $CI->Subscription_model->insert_subscription($data);

            // Update the license status in the License model
            $CI->load->model('License');
            $CI->License->update_license_status();

            // Redirect to the thank-you page
            redirect(base_url('License/thanks_image'));
            exit;
        } else {
            // Redirect back with an error message if the ID is invalid
            redirect(base_url('License/renew'));
            exit;
        }
    }
}
