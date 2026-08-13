<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Production_dashboard_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Recipe_model');
        $this->load->model('Recipe_item_model');
        $this->load->model('Items_model');
    }

    /**
     * ========================
     * TODAY'S PRODUCTIONS
     * ========================
     */

    /**
     * Get today's productions count
     */
    public function get_today_count()
    {
        $this->db->where('DATE(created_at)', date('Y-m-d'));
        return (int) $this->db->count_all_results('production_batches');
    }

    /**
     * Get today's approved productions count
     */
    public function get_today_approved_count()
    {
        $this->db->where('DATE(created_at)', date('Y-m-d'));
        $this->db->where('status', 'Approved');
        return (int) $this->db->count_all_results('production_batches');
    }

    /**
     * Get today's production details
     */
    public function get_today_productions()
    {
        $this->db->select('
            pb.*, 
            r.recipe_name, 
            r.yield_quantity,
            r.output_product_id,
            i.item_name as output_product_name,
            u.username as created_by_name,
            (r.yield_quantity * pb.batch_quantity) as total_output_qty
        ');
        $this->db->from('production_batches as pb');
        $this->db->join('recipes as r', 'r.id = pb.recipe_id', 'left');
        $this->db->join('db_items as i', 'i.id = r.output_product_id', 'left');
        $this->db->join('db_users as u', 'u.id = pb.created_by', 'left');
        $this->db->where('DATE(pb.created_at)', date('Y-m-d'));
        $this->db->order_by('pb.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * ========================
     * TOTAL PRODUCTIONS
     * ========================
     */

    /**
     * Get total productions count (lifetime)
     */
    public function get_total_count()
    {
        return (int) $this->db->count_all_results('production_batches');
    }

    /**
     * Get total approved productions count
     */
    public function get_total_approved_count()
    {
        $this->db->where('status', 'Approved');
        return (int) $this->db->count_all_results('production_batches');
    }

    /**
     * Get total output quantity (lifetime)
     */
    public function get_total_output_quantity()
    {
        $this->db->select('SUM(produced_quantity) as total_output');
        $this->db->where('status', 'Approved');
        $result = $this->db->get('production_batches')->row();
        return (float) ($result->total_output ?? 0);
    }

    /**
     * Get total cost of all productions
     */
    public function get_total_cost()
    {
        $this->db->select('SUM(total_cost) as total_cost');
        $this->db->where('status', 'Approved');
        $result = $this->db->get('production_batches')->row();
        return (float) ($result->total_cost ?? 0);
    }

    /**
     * Get lifetime production summary
     */
    public function get_total_summary()
    {
        $this->db->select('
            COUNT(*) as total_batches,
            SUM(CASE WHEN status = "Approved" THEN 1 ELSE 0 END) as approved_batches,
            SUM(CASE WHEN status = "Draft" THEN 1 ELSE 0 END) as draft_batches,
            SUM(CASE WHEN status = "Cancelled" THEN 1 ELSE 0 END) as cancelled_batches,
            SUM(produced_quantity) as total_output,
            SUM(total_cost) as total_cost,
            AVG(cost_per_unit) as avg_cost_per_unit
        ');
        $result = $this->db->get('production_batches')->row();

        return (object) array(
            'total_batches' => (int) ($result->total_batches ?? 0),
            'approved_batches' => (int) ($result->approved_batches ?? 0),
            'draft_batches' => (int) ($result->draft_batches ?? 0),
            'cancelled_batches' => (int) ($result->cancelled_batches ?? 0),
            'total_output' => (float) ($result->total_output ?? 0),
            'total_cost' => (float) ($result->total_cost ?? 0),
            'avg_cost_per_unit' => (float) ($result->avg_cost_per_unit ?? 0)
        );
    }

    /**
     * ========================
     * DATE RANGE PRODUCTIONS
     * ========================
     */

    /**
     * Get productions by date range
     * @param string $from_date (format: Y-m-d)
     * @param string $to_date (format: Y-m-d)
     * @param array $filters Optional filters (status, recipe_id, warehouse_id)
     */
    public function get_by_date_range($from_date, $to_date, $filters = array())
    {
        $this->db->select('
            pb.*, 
            r.recipe_name, 
            r.yield_quantity,
            r.output_product_id,
            i.item_name as output_product_name,
            u.username as created_by_name,
            (r.yield_quantity * pb.batch_quantity) as total_output_qty
        ');
        $this->db->from('production_batches as pb');
        $this->db->join('recipes as r', 'r.id = pb.recipe_id', 'left');
        $this->db->join('db_items as i', 'i.id = r.output_product_id', 'left');
        $this->db->join('db_users as u', 'u.id = pb.created_by', 'left');
        
        $this->db->where('DATE(pb.created_at) >=', $from_date);
        $this->db->where('DATE(pb.created_at) <=', $to_date);
        
        // Apply optional filters
        if (isset($filters['status']) && $filters['status']) {
            $this->db->where('pb.status', $filters['status']);
        }
        
        if (isset($filters['recipe_id']) && $filters['recipe_id']) {
            $this->db->where('pb.recipe_id', $filters['recipe_id']);
        }
        
        if (isset($filters['warehouse_id']) && $filters['warehouse_id']) {
            $this->db->where('pb.warehouse_id', $filters['warehouse_id']);
        }
        
        $this->db->order_by('pb.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get production summary for date range
     * @param string $from_date (format: Y-m-d)
     * @param string $to_date (format: Y-m-d)
     * @param array $filters Optional filters
     */
    public function get_date_range_summary($from_date, $to_date, $filters = array())
    {
        $this->db->select('
            COUNT(*) as total_batches,
            SUM(CASE WHEN status = "Approved" THEN 1 ELSE 0 END) as approved_batches,
            SUM(CASE WHEN status = "Draft" THEN 1 ELSE 0 END) as draft_batches,
            SUM(CASE WHEN status = "Cancelled" THEN 1 ELSE 0 END) as cancelled_batches,
            SUM(produced_quantity) as total_output,
            SUM(total_cost) as total_cost,
            AVG(cost_per_unit) as avg_cost_per_unit,
            MIN(created_at) as first_batch_date,
            MAX(created_at) as last_batch_date
        ');
        $this->db->from('production_batches');
        
        $this->db->where('DATE(created_at) >=', $from_date);
        $this->db->where('DATE(created_at) <=', $to_date);
        
        // Apply optional filters
        if (isset($filters['status']) && $filters['status']) {
            $this->db->where('status', $filters['status']);
        }
        
        if (isset($filters['recipe_id']) && $filters['recipe_id']) {
            $this->db->where('recipe_id', $filters['recipe_id']);
        }
        
        if (isset($filters['warehouse_id']) && $filters['warehouse_id']) {
            $this->db->where('warehouse_id', $filters['warehouse_id']);
        }
        
        $result = $this->db->get()->row();
        
        return (object) array(
            'total_batches' => (int) ($result->total_batches ?? 0),
            'approved_batches' => (int) ($result->approved_batches ?? 0),
            'draft_batches' => (int) ($result->draft_batches ?? 0),
            'cancelled_batches' => (int) ($result->cancelled_batches ?? 0),
            'total_output' => (float) ($result->total_output ?? 0),
            'total_cost' => (float) ($result->total_cost ?? 0),
            'avg_cost_per_unit' => (float) ($result->avg_cost_per_unit ?? 0),
            'first_batch_date' => $result->first_batch_date ?? null,
            'last_batch_date' => $result->last_batch_date ?? null
        );
    }

    /**
     * ========================
     * ITEM USAGE REPORT
     * ========================
     */

    /**
     * Get item usage report in productions within date range
     * @param int $item_id Item ID to search
     * @param string $from_date (format: Y-m-d)
     * @param string $to_date (format: Y-m-d)
     * @param string $status Optional: Approved, Draft, Cancelled, or empty for all
     */
    public function get_item_usage_report($item_id, $from_date, $to_date, $status = '')
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
        $this->db->where('DATE(pb.created_at) >=', $from_date);
        $this->db->where('DATE(pb.created_at) <=', $to_date);
        
        // Apply status filter if provided
        if (!empty($status)) {
            $this->db->where('pb.status', $status);
        }
        
        $this->db->order_by('pb.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get item usage summary for date range
     * @param int $item_id Item ID to search
     * @param string $from_date (format: Y-m-d)
     * @param string $to_date (format: Y-m-d)
     * @param string $status Optional: Approved, Draft, Cancelled, or empty for all
     */
    public function get_item_usage_summary($item_id, $from_date, $to_date, $status = '')
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
        $this->db->where('DATE(pb.created_at) >=', $from_date);
        $this->db->where('DATE(pb.created_at) <=', $to_date);
        
        // Apply status filter if provided
        if (!empty($status)) {
            $this->db->where('pb.status', $status);
        }
        
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
     * ========================
     * UTILITY METHODS
     * ========================
     */

    /**
     * Get all recipes for dropdown
     */
    public function get_all_recipes()
    {
        $this->db->select('id, recipe_name');
        $this->db->order_by('recipe_name', 'ASC');
        return $this->db->get('recipes')->result();
    }

    /**
     * Get all items for dropdown (used in productions)
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
