<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class License extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function check_license() {
        // Check if license record exists
        if (!$this->db->count_all_results('db_license')) {
            return false;
        }

        // Check if there are any inactive licenses
        if ($this->db->where('status !=', 1)->count_all_results('db_license') > 0) {
            return false;
        }

        $current_time = date('Y-m-d H:i:s');
        $expired_license = $this->db
        ->where('expired_at <', $current_time)
        ->where('status', 1)
        ->limit(1)
        ->get('db_license');
            
        if ($expired_license->num_rows() === 1) {
            $this->db
                ->set('status', 0)
                ->where('status', 1)
                ->update('db_license');
            return false;
        }

        return true;
    }
    public function update_license_status() {
        // Set expired_at to one month from today's date
      
        $new_expiration_date = date('Y-m-d H:i:s', strtotime('+1 month'));

        // Update license table
        $this->db->set('status', 1)
                 ->set('expired_at', $new_expiration_date)
                 ->where('status', 0)  // Assuming a single license record with ID=1
                 ->update('db_license');
    }
}
