<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Recipe_model extends CI_Model
{

    public $table = 'recipes';
    public $id = 'id';

    // Columns for DataTables ordering and searching
    private $column_order = array(null, 'recipe_name', 'i.item_name', 'yield_quantity', 'notes', 'created_by', 'created_at'); // Updated to include item_name
    private $column_search = array('recipe_name', 'notes', 'i.item_name'); // Added item_name for search
    private $order = array('id' => 'desc'); // default order

    public function __construct()
    {
        parent::__construct();
    }

    // Get all recipes
    public function get_all()
    {
        return $this->db->get($this->table)->result();
    }

    // Get recipe by ID
    public function get_by_id($id)
    {
        $this->db->where($this->id, $id);
        return $this->db->get($this->table)->row();
    }

    // Insert new recipe
    public function insert($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    // Update recipe
    public function update($id, $data)
    {
        $this->db->where($this->id, $id);
        $this->db->update($this->table, $data);
        return $this->db->affected_rows();
    }

    // Delete recipe
    public function delete($id)
    {
        $this->db->where($this->id, $id);
        $this->db->delete($this->table);
        return $this->db->affected_rows();
    }

    // Return total number of recipes (used by controller / datatables)
    public function count_all()
    {
        return (int) $this->db->count_all($this->table);
    }

    /**
     * Build DataTables filtered query - UPDATED WITH JOIN FOR SEARCH
     */
    private function _get_datatables_query()
    {
        // Select specific columns to avoid ambiguity
        $this->db->select('recipes.*, i.item_name as output_product_name');
        $this->db->from($this->table);
        $this->db->join('db_items as i', 'i.id = recipes.output_product_id', 'left');

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

    /**
     * Get recipe and its items with item details (unit, price, stock)
     * returns array: ['recipe' => row, 'items' => array]
     */
    public function get_with_items($recipe_id)
    {
        $recipe = $this->get_by_id($recipe_id);
        // fetch recipe items joined with items table
        $this->db->select('ri.id as recipe_item_id, ri.item_id, ri.quantity, ri.unit as recipe_unit, i.item_name, i.item_code, i.purchase_price, i.stock, u.unit_name');
        $this->db->from('recipe_items as ri');
        $this->db->join('db_items as i', 'i.id = ri.item_id', 'left');
        $this->db->join('db_units as u', 'u.id = i.unit_id', 'left');
        $this->db->where('ri.recipe_id', $recipe_id);
        $items = $this->db->get()->result();

        return ['recipe' => $recipe, 'items' => $items];
    }
}
