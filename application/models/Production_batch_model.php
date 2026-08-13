<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Production_batch_model extends CI_Model
{

    public $table = 'production_batches';
    public $id = 'id';

    // Columns for DataTables ordering and searching
    private $column_order = array(null, 'production_batches.batch_code', 'production_batches.recipe_id', 'production_batches.batch_quantity', 'production_batches.status', 'production_batches.created_by', 'production_batches.created_at');
    private $column_search = array('production_batches.batch_code', 'production_batches.notes', 'r.recipe_name');
    private $order = array('id' => 'desc');

    public function __construct()
    {
        parent::__construct();
    }

    // Get all production batches
    public function get_all()
    {
        return $this->db->get($this->table)->result();
    }

    // Get production batch by ID
    public function get_by_id($id)
    {
        $this->db->select('pb.*, r.recipe_name, r.yield_quantity, r.output_product_id, r.overhead_cost, r.overhead_cost_type, i.item_name as output_product_name');
        $this->db->from('production_batches as pb');
        $this->db->join('recipes as r', 'r.id = pb.recipe_id', 'left');
        $this->db->join('db_items as i', 'i.id = r.output_product_id', 'left');
        $this->db->where('pb.id', $id);
        return $this->db->get()->row();
    }

    // Insert new production batch
    public function insert($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    // Update production batch
    public function update($id, $data)
    {
        $this->db->where($this->id, $id);
        $this->db->update($this->table, $data);

        if ($this->db->error()['code'] != 0) {
            return false;
        }

        return true;
    }

    // Delete production batch
    public function delete($id)
    {
        $this->db->where($this->id, $id);
        $this->db->delete($this->table);
        return $this->db->affected_rows();
    }

    // Get production batches by status
    public function get_by_status($status)
    {
        $this->db->where('status', $status);
        return $this->db->get($this->table)->result();
    }

    // Return total number of production batches
    public function count_all()
    {
        return (int) $this->db->count_all($this->table);
    }

    /**
     * Build DataTables filtered query
     */
    private function _get_datatables_query()
    {
        // Join with recipes table to get recipe details and output product
        $this->db->select('
            production_batches.*, 
            r.recipe_name, 
            r.yield_quantity,
            r.output_product_id,
            i.item_name as output_product_name,
            (r.yield_quantity * production_batches.batch_quantity) as total_output_qty,
            production_batches.total_cost,
            production_batches.cost_per_unit
        ');
        $this->db->from($this->table);
        $this->db->join('recipes as r', 'r.id = production_batches.recipe_id', 'left');
        $this->db->join('db_items as i', 'i.id = r.output_product_id', 'left');

        $i = 0;
        $search_value = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

        if ($search_value) {
            foreach ($this->column_search as $item) {
                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $search_value);
                } else {
                    $this->db->or_like($item, $search_value);
                }

                // Close the group after the last item
                if ((int)(count($this->column_search) - 1) === (int)$i) {
                    $this->db->group_end();
                }
                $i++;
            }
        }

        // Ordering
        if (isset($_POST['order']) && count($_POST['order'])) {
            $order_col_index = intval($_POST['order'][0]['column']);
            $order_dir = $_POST['order'][0]['dir'];
            if (isset($this->column_order[$order_col_index]) && $this->column_order[$order_col_index] !== null) {
                $this->db->order_by($this->column_order[$order_col_index], $order_dir);
            }
        } elseif ($this->order) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    /**
     * Get filtered datatables rows
     */
    public function get_datatables()
    {
        $this->_get_datatables_query();

        if (isset($_POST['length']) && (int)$_POST['length'] !== -1) {
            $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
            $length = intval($_POST['length']);
            $this->db->limit($length, $start);
        }

        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Count records after filtering (for DataTables)
     */
    public function count_filtered()
    {
        $this->_get_datatables_query();
        return $this->db->count_all_results();
    }

    /**
     * DASHBOARD METHODS
     */

    /**
     * Get today's productions count
     */
    public function get_today_productions_count()
    {
        $this->db->where('DATE(created_at)', date('Y-m-d'));
        return (int) $this->db->count_all_results($this->table);
    }

    /**
     * Get today's approved productions count
     */
    public function get_today_approved_count()
    {
        $this->db->where('DATE(created_at)', date('Y-m-d'));
        $this->db->where('status', 'Approved');
        return (int) $this->db->count_all_results($this->table);
    }

    /**
     * Get total productions count (lifetime)
     */
    public function get_total_productions_count()
    {
        return $this->count_all();
    }

    /**
     * Get total output quantity (lifetime)
     */
    public function get_total_output_quantity()
    {
        $this->db->select('SUM(produced_quantity) as total_output');
        $this->db->where('status', 'Approved');
        $result = $this->db->get($this->table)->row();
        return (float) ($result->total_output ?? 0);
    }

    /**
     * Get today's production details
     */
    public function get_today_productions()
    {
        $this->db->select('
            production_batches.*, 
            r.recipe_name, 
            r.yield_quantity,
            r.output_product_id,
            i.item_name as output_product_name,
            u.username as created_by_name
        ');
        $this->db->from($this->table);
        $this->db->join('recipes as r', 'r.id = production_batches.recipe_id', 'left');
        $this->db->join('db_items as i', 'i.id = r.output_product_id', 'left');
        $this->db->join('db_users as u', 'u.id = production_batches.created_by', 'left');
        $this->db->where('DATE(production_batches.created_at)', date('Y-m-d'));
        $this->db->order_by('production_batches.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get productions by date range
     * @param string $from_date (format: Y-m-d)
     * @param string $to_date (format: Y-m-d)
     * @param array $filters Optional filters (status, recipe_id, warehouse_id)
     */
    public function get_productions_by_date_range($from_date, $to_date, $filters = array())
    {
        $this->db->select('
            production_batches.*, 
            r.recipe_name, 
            r.yield_quantity,
            r.output_product_id,
            i.item_name as output_product_name,
            u.username as created_by_name,
            (r.yield_quantity * production_batches.batch_quantity) as total_output_qty
        ');
        $this->db->from($this->table);
        $this->db->join('recipes as r', 'r.id = production_batches.recipe_id', 'left');
        $this->db->join('db_items as i', 'i.id = r.output_product_id', 'left');
        $this->db->join('db_users as u', 'u.id = production_batches.created_by', 'left');
        
        $this->db->where('DATE(production_batches.created_at) >=', $from_date);
        $this->db->where('DATE(production_batches.created_at) <=', $to_date);
        
        // Apply optional filters
        if (isset($filters['status']) && $filters['status']) {
            $this->db->where('production_batches.status', $filters['status']);
        }
        
        if (isset($filters['recipe_id']) && $filters['recipe_id']) {
            $this->db->where('production_batches.recipe_id', $filters['recipe_id']);
        }
        
        if (isset($filters['warehouse_id']) && $filters['warehouse_id']) {
            $this->db->where('production_batches.warehouse_id', $filters['warehouse_id']);
        }
        
        $this->db->order_by('production_batches.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get production summary for date range
     * @param string $from_date (format: Y-m-d)
     * @param string $to_date (format: Y-m-d)
     * @param array $filters Optional filters
     */
    public function get_production_summary($from_date, $to_date, $filters = array())
    {
        $this->db->select('
            COUNT(DISTINCT production_batches.id) as total_batches,
            SUM(CASE WHEN production_batches.status = "Approved" THEN 1 ELSE 0 END) as approved_batches,
            SUM(CASE WHEN production_batches.status = "Draft" THEN 1 ELSE 0 END) as draft_batches,
            SUM(CASE WHEN production_batches.status = "Cancelled" THEN 1 ELSE 0 END) as cancelled_batches,
            SUM(production_batches.produced_quantity) as total_output,
            SUM(production_batches.total_cost) as total_cost,
            AVG(production_batches.cost_per_unit) as avg_cost_per_unit,
            MIN(production_batches.created_at) as first_batch_date,
            MAX(production_batches.created_at) as last_batch_date
        ');
        $this->db->from($this->table);
        
        $this->db->where('DATE(production_batches.created_at) >=', $from_date);
        $this->db->where('DATE(production_batches.created_at) <=', $to_date);
        
        // Apply optional filters
        if (isset($filters['status']) && $filters['status']) {
            $this->db->where('production_batches.status', $filters['status']);
        }
        
        if (isset($filters['recipe_id']) && $filters['recipe_id']) {
            $this->db->where('production_batches.recipe_id', $filters['recipe_id']);
        }
        
        if (isset($filters['warehouse_id']) && $filters['warehouse_id']) {
            $this->db->where('production_batches.warehouse_id', $filters['warehouse_id']);
        }
        
        $result = $this->db->get()->row();
        
        // Ensure numeric values
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
     * Get item usage report in productions within date range
     * @param int $item_id Item ID to search
     * @param string $from_date (format: Y-m-d)
     * @param string $to_date (format: Y-m-d)
     */
    public function get_item_usage_report($item_id, $from_date, $to_date)
    {
        $this->db->select('
            production_batches.id,
            production_batches.batch_code,
            production_batches.batch_quantity,
            production_batches.status,
            production_batches.created_at,
            r.recipe_name,
            i.item_name,
            i.item_code,
            ri.quantity_per_batch,
            (production_batches.batch_quantity * ri.quantity_per_batch) as total_consumed,
            u.username as created_by_name
        ');
        $this->db->from($this->table);
        $this->db->join('recipes as r', 'r.id = production_batches.recipe_id', 'left');
        $this->db->join('recipe_items as ri', 'r.id = ri.recipe_id', 'left');
        $this->db->join('db_items as i', 'i.id = ri.item_id', 'left');
        $this->db->join('db_users as u', 'u.id = production_batches.created_by', 'left');
        
        $this->db->where('ri.item_id', $item_id);
        $this->db->where('DATE(production_batches.created_at) >=', $from_date);
        $this->db->where('DATE(production_batches.created_at) <=', $to_date);
        $this->db->where('production_batches.status', 'Approved');
        
        $this->db->order_by('production_batches.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get item usage summary for date range
     * @param int $item_id Item ID to search
     * @param string $from_date (format: Y-m-d)
     * @param string $to_date (format: Y-m-d)
     */
    public function get_item_usage_summary($item_id, $from_date, $to_date)
    {
        $this->db->select('
            COUNT(DISTINCT production_batches.id) as production_count,
            SUM(production_batches.batch_quantity * ri.quantity_per_batch) as total_consumed,
            AVG(production_batches.batch_quantity * ri.quantity_per_batch) as avg_per_batch,
            MIN(production_batches.batch_quantity * ri.quantity_per_batch) as min_per_batch,
            MAX(production_batches.batch_quantity * ri.quantity_per_batch) as max_per_batch
        ');
        $this->db->from($this->table);
        $this->db->join('recipes as r', 'r.id = production_batches.recipe_id', 'left');
        $this->db->join('recipe_items as ri', 'r.id = ri.recipe_id', 'left');
        
        $this->db->where('ri.item_id', $item_id);
        $this->db->where('DATE(production_batches.created_at) >=', $from_date);
        $this->db->where('DATE(production_batches.created_at) <=', $to_date);
        $this->db->where('production_batches.status', 'Approved');
        
        $result = $this->db->get()->row();
        
        return (object) array(
            'production_count' => (int) ($result->production_count ?? 0),
            'total_consumed' => (float) ($result->total_consumed ?? 0),
            'avg_per_batch' => (float) ($result->avg_per_batch ?? 0),
            'min_per_batch' => (float) ($result->min_per_batch ?? 0),
            'max_per_batch' => (float) ($result->max_per_batch ?? 0)
        );
    }
}
