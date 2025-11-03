<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Inventory_movements_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function insert($data)
    {
        $this->db->insert('inventory_movements', $data);
        return $this->db->insert_id();
    }

    // Add method to get movements by reference
    public function get_by_reference($type, $reference_id)
    {
        $this->db->where('type', $type);
        $this->db->where('reference_id', $reference_id);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('inventory_movements')->result();
    }
}
