<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Production_batch_model extends CI_Model
{

    public $table = 'production_batches';
    public $id = 'id';

    // Columns for DataTables ordering and searching
    private $column_order = array(null, 'batch_code', 'recipe_id', 'batch_quantity', 'status', 'created_by', 'created_at');
    private $column_search = array('batch_code', 'notes', 'r.recipe_name');
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
        $this->db->select('pb.*, r.recipe_name, r.yield_quantity, r.output_product_id, i.item_name as output_product_name');
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
        return $this->db->affected_rows();
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
                if (count($this->column_search) - 1 == $i) {
                    $this->db->group_end();
                }
                $i++;
            }
        }

        // Ordering
        if (isset($_POST['order']) && count($_POST['order'])) {
            $order_col_index = intval($_POST['order'][0]['column']);
            $order_dir = $_POST['order'][0]['dir'];
            if (isset($this->column_order[$order_col_index]) && $this->column_order[$order_col_index] != null) {
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

        if (isset($_POST['length']) && $_POST['length'] != -1) {
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
}
