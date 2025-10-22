<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Subscription extends MY_Controller {
    
    public function __construct(){
        parent::__construct();
        $this->load_global(); // Load global settings or resources
        $this->load->model('Subscription_model', 'subscription'); // Load the Subscription model
    }

    // Method to display subscription data
    public function view(){
        // $this->permission_check('view_subscription'); // Check permission

        // Retrieve subscription data from the model
        $data = $this->data;
        $data['page_title'] = $this->lang->line('subscription');
        $data['subscriptions'] = $this->subscription->get_all_subscriptions();

        // Load the subscription view with data
        $this->load->view('subscription_view', $data);
    }
}
