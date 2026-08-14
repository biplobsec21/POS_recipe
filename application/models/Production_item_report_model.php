<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Production_item_report_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get item usage report in productions within date range
     * Always uses Approved status, filtered by approved_at date
     * @param int $item_id Item ID to search
     * @param string $from_date (format: Y-m-d)
     * @param string $to_date (format: Y-m-d)
     */
    public function get_item_usage_report($item_id, $from_date, $to_date)
    {
        $this->db->select('
            pb.id,
            pb.batch_code,
            pb.batch_quantity,
            pb.status,
            pb.created_at,
            r.recipe_name,
            i.item_name,
            i.item_code,
            ri.quantity as quantity_per_batch,
            (pb.batch_quantity * ri.quantity) as total_consumed,
            u.username as created_by_name
        ');
        $this->db->from('production_batches as pb');
        $this->db->join('recipes as r', 'r.id = pb.recipe_id', 'left');
        $this->db->join('recipe_items as ri', 'r.id = ri.recipe_id', 'left');
        $this->db->join('db_items as i', 'i.id = ri.item_id', 'left');
        $this->db->join('db_users as u', 'u.id = pb.created_by', 'left');
        
        $this->db->where('ri.item_id', $item_id);
        $this->db->where('pb.status', 'Approved');
        $this->db->where('DATE(pb.approved_at) >=', $from_date);
        $this->db->where('DATE(pb.approved_at) <=', $to_date);
        
        $this->db->order_by('pb.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get item usage summary for date range
     * Always uses Approved status, filtered by approved_at date
     * @param int $item_id Item ID to search
     * @param string $from_date (format: Y-m-d)
     * @param string $to_date (format: Y-m-d)
     */
    public function get_item_usage_summary($item_id, $from_date, $to_date)
    {
        $this->db->select('
            COUNT(DISTINCT pb.id) as production_count,
            SUM(pb.batch_quantity * ri.quantity) as total_consumed,
            AVG(pb.batch_quantity * ri.quantity) as avg_per_batch,
            MIN(pb.batch_quantity * ri.quantity) as min_per_batch,
            MAX(pb.batch_quantity * ri.quantity) as max_per_batch,
            i.item_name,
            i.item_code
        ');
        $this->db->from('production_batches as pb');
        $this->db->join('recipes as r', 'r.id = pb.recipe_id', 'left');
        $this->db->join('recipe_items as ri', 'r.id = ri.recipe_id', 'left');
        $this->db->join('db_items as i', 'i.id = ri.item_id', 'left');
        
        $this->db->where('ri.item_id', $item_id);
        $this->db->where('pb.status', 'Approved');
        $this->db->where('DATE(pb.approved_at) >=', $from_date);
        $this->db->where('DATE(pb.approved_at) <=', $to_date);
        
        $result = $this->db->get()->row();
        
        return (object) array(
            'item_name' => $result->item_name ?? 'Unknown',
            'item_code' => $result->item_code ?? 'N/A',
            'production_count' => (int) ($result->production_count ?? 0),
            'total_consumed' => (float) ($result->total_consumed ?? 0),
            'avg_per_batch' => (float) ($result->avg_per_batch ?? 0),
            'min_per_batch' => (float) ($result->min_per_batch ?? 0),
            'max_per_batch' => (float) ($result->max_per_batch ?? 0)
        );
    }

    /**
     * Get all production items (items used in recipes)
     */
    public function get_all_production_items()
    {
        $this->db->select('i.id, i.item_code, i.item_name', FALSE);
        $this->db->from('db_items as i');
        $this->db->join('recipe_items as ri', 'ri.item_id = i.id', 'inner');
        $this->db->distinct();
        $this->db->order_by('i.item_name', 'ASC');
        $query = $this->db->get();
        
        if ($query === FALSE) {
            return array();
        }
        
        return $query->result();
    }
}
