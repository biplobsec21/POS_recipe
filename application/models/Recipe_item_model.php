<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Recipe_item_model extends CI_Model {

    public $table = 'recipe_items';
    public $id = 'id';

    public function __construct() {
        parent::__construct();
    }

    // Get all recipe items for a specific recipe
    public function get_by_recipe_id($recipe_id) {
        $this->db->where('recipe_id', $recipe_id);
        return $this->db->get($this->table)->result();
    }

    // Insert new recipe item
    public function insert($data) {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    // Update recipe item
    public function update($id, $data) {
        $this->db->where($this->id, $id);
        $this->db->update($this->table, $data);
        return $this->db->affected_rows();
    }

    // Delete recipe item
    public function delete($id) {
        $this->db->where($this->id, $id);
        $this->db->delete($this->table);
        return $this->db->affected_rows();
    }

    // Delete all recipe items for a specific recipe
    public function delete_by_recipe_id($recipe_id) {
        $this->db->where('recipe_id', $recipe_id);
        $this->db->delete($this->table);
        return $this->db->affected_rows();
    }
}
