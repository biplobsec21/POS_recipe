<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Subscription_model extends CI_Model {

    public function insert_subscription($data) {
        $this->db->insert('tbl_subscription', $data);
    }
    public function update_license_status() {
       $start_date = date('Y-m-d 00:00:00');

        // Calculate the expiration date (end date) one month from today, set to the end of the day
        $end_date = date('Y-m-d 23:59:59', strtotime('+1 month -1 day'));

        // Create the description with the start and end dates
        $description = "License start from $start_date to $end_date";

        // Update the license record in the database
        $this->db->set('status', 1)
                 ->set('expired_at', $end_date)
                 ->set('description', $description)
                 ->where('status', 0)  // Assuming a single license record with ID=1
                 ->update('db_license');

    }
    public function get_all_subscriptions() {
        // Fetch all records from tbl_subscription
        return $this->db->get('tbl_subscription')->result_array();
    }
}
