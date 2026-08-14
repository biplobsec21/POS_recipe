<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Production_summary_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get material input summary for date range
     * Groups all materials used in production within the specified period
     * Only includes Approved batches
     * @param string $from_date (format: Y-m-d)
     * @param string $to_date (format: Y-m-d)
     * @param string $recipe_id Optional: filter by recipe
     * @param string $item_id Optional: filter by material item
     */
    public function get_material_summary($from_date, $to_date, $recipe_id = '', $item_id = '')
    {
        $this->db->select('
            i.id as item_id,
            i.item_code,
            i.item_name,
            u.unit_name as unit,
            SUM(pb.batch_quantity * ri.quantity) as total_used,
            COUNT(DISTINCT pb.id) as total_batches
        ');
        
        $this->db->from('production_batches as pb');
        $this->db->join('recipes as r', 'r.id = pb.recipe_id', 'left');
        $this->db->join('recipe_items as ri', 'r.id = ri.recipe_id', 'left');
        $this->db->join('db_items as i', 'i.id = ri.item_id', 'left');
        $this->db->join('db_units as u', 'u.id = i.unit_id', 'left');
        
        $this->db->where('DATE(pb.created_at) >=', $from_date);
        $this->db->where('DATE(pb.created_at) <=', $to_date);
        $this->db->where('pb.status', 'Approved');
        
        // Filter by recipe if provided
        if (!empty($recipe_id)) {
            $this->db->where('pb.recipe_id', $recipe_id);
        }
        
        // Filter by item if provided
        if (!empty($item_id)) {
            $this->db->where('ri.item_id', $item_id);
        }
        
        $this->db->where('ri.item_id IS NOT NULL');
        $this->db->group_by('i.id');
        $this->db->order_by('i.item_name', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get output products summary for date range
     * Shows all finished products created in the specified period
     * Only includes Approved batches
     * @param string $from_date (format: Y-m-d)
     * @param string $to_date (format: Y-m-d)
     * @param string $recipe_id Optional: filter by recipe
     * @param string $item_id Optional: filter by material item
     */
    public function get_output_summary($from_date, $to_date, $recipe_id = '', $item_id = '')
    {
        $this->db->select('
            r.id as recipe_id,
            r.recipe_name,
            i.item_name as output_product_name,
            u.unit_name as output_unit,
            r.yield_quantity,
            SUM(pb.batch_quantity) as total_produced,
            AVG(pb.batch_quantity) as avg_per_batch,
            COUNT(DISTINCT pb.id) as total_batches,
            COUNT(DISTINCT ri.item_id) as unique_items_used
        ');
        
        $this->db->from('production_batches as pb');
        $this->db->join('recipes as r', 'r.id = pb.recipe_id', 'left');
        $this->db->join('db_items as i', 'i.id = r.output_product_id', 'left');
        $this->db->join('db_units as u', 'u.id = i.unit_id', 'left');
        $this->db->join('recipe_items as ri', 'r.id = ri.recipe_id', 'left');
        
        $this->db->where('DATE(pb.created_at) >=', $from_date);
        $this->db->where('DATE(pb.created_at) <=', $to_date);
        $this->db->where('pb.status', 'Approved');
        
        // Filter by recipe if provided
        if (!empty($recipe_id)) {
            $this->db->where('pb.recipe_id', $recipe_id);
        }
        
        // Filter by item if provided - show only recipes that use this material
        if (!empty($item_id)) {
            $this->db->where('ri.item_id', $item_id);
        }
        
        $this->db->group_by('pb.recipe_id');
        $this->db->order_by('r.recipe_name', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get production summary statistics
     * Overall stats for the period (Approved batches only)
     * @param string $from_date (format: Y-m-d)
     * @param string $to_date (format: Y-m-d)
     * @param string $recipe_id Optional: filter by recipe
     * @param string $item_id Optional: filter by material item
     */
    public function get_production_summary_stats($from_date, $to_date, $recipe_id = '', $item_id = '')
    {
        $this->db->select('
            COUNT(DISTINCT pb.id) as total_batches,
            COUNT(DISTINCT pb.recipe_id) as total_recipes,
            COUNT(DISTINCT ri.item_id) as total_unique_materials,
            SUM(pb.batch_quantity) as total_output_quantity
        ');
        
        $this->db->from('production_batches as pb');
        $this->db->join('recipe_items as ri', 'ri.recipe_id = pb.recipe_id', 'left');
        
        $this->db->where('DATE(pb.created_at) >=', $from_date);
        $this->db->where('DATE(pb.created_at) <=', $to_date);
        $this->db->where('pb.status', 'Approved');
        
        // Filter by recipe if provided
        if (!empty($recipe_id)) {
            $this->db->where('pb.recipe_id', $recipe_id);
        }
        
        // Filter by item if provided
        if (!empty($item_id)) {
            $this->db->where('ri.item_id', $item_id);
        }
        
        $result = $this->db->get()->row();
        
        return (object) array(
            'total_batches' => (int) ($result->total_batches ?? 0),
            'total_recipes' => (int) ($result->total_recipes ?? 0),
            'total_unique_materials' => (int) ($result->total_unique_materials ?? 0),
            'total_output_quantity' => (float) ($result->total_output_quantity ?? 0)
        );
    }

    /**
     * Get batch details for a specific material item within date range and filters
     * @param int $item_id
     * @param string $from_date (format: Y-m-d)
     * @param string $to_date (format: Y-m-d)
     * @param string $recipe_id Optional: filter by recipe
     */
    public function get_item_batch_details($item_id, $from_date, $to_date, $recipe_id = '')
    {
        $this->db->select('
            pb.id as batch_id,
            pb.batch_code,
            r.recipe_name,
            di.item_name as output_product,
            du.unit_name as output_unit,
            pb.batch_quantity,
            ri.quantity as item_quantity_per_batch,
            (pb.batch_quantity * ri.quantity) as total_item_used,
            DATE(pb.created_at) as batch_date,
            TIME(pb.created_at) as batch_time,
            pb.status,
            mi.item_name as material_name,
            mu.unit_name as material_unit
        ');
        
        $this->db->from('production_batches as pb');
        $this->db->join('recipes as r', 'r.id = pb.recipe_id', 'left');
        $this->db->join('recipe_items as ri', 'r.id = ri.recipe_id AND ri.item_id = ' . (int)$item_id, 'left');
        $this->db->join('db_items as di', 'di.id = r.output_product_id', 'left');
        $this->db->join('db_units as du', 'du.id = di.unit_id', 'left');
        $this->db->join('db_items as mi', 'mi.id = ' . (int)$item_id, 'left');
        $this->db->join('db_units as mu', 'mu.id = mi.unit_id', 'left');
        
        $this->db->where('DATE(pb.created_at) >=', $from_date);
        $this->db->where('DATE(pb.created_at) <=', $to_date);
        $this->db->where('pb.status', 'Approved');
        $this->db->where('ri.item_id', $item_id);
        
        // Filter by recipe if provided
        if (!empty($recipe_id)) {
            $this->db->where('pb.recipe_id', $recipe_id);
        }
        
        $this->db->order_by('pb.created_at', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Get all materials/ingredients for a specific recipe
     * @param int $recipe_id
     */
    public function get_recipe_materials($recipe_id)
    {
        $this->db->select('
            ri.id,
            ri.item_id,
            i.item_code,
            i.item_name,
            u.unit_name as unit,
            ri.quantity as quantity_per_batch,
            i.purchase_price,
            (ri.quantity * i.purchase_price) as material_cost
        ');
        
        $this->db->from('recipe_items as ri');
        $this->db->join('db_items as i', 'i.id = ri.item_id', 'left');
        $this->db->join('db_units as u', 'u.id = i.unit_id', 'left');
        
        $this->db->where('ri.recipe_id', $recipe_id);
        $this->db->order_by('i.item_name', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get all materials/items for filter dropdown
     */
    public function get_all_materials()
    {
        $this->db->select('i.id, i.item_code, i.item_name', false);
        $this->db->from('recipe_items as ri');
        $this->db->join('db_items as i', 'i.id = ri.item_id', 'left');
        $this->db->group_by('i.id');
        $this->db->order_by('i.item_name', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get all recipes for filter dropdown
     */
    public function get_all_recipes()
    {
        $this->db->select('r.id, r.recipe_name, i.item_name as output_product_name');
        $this->db->from('recipes as r');
        $this->db->join('db_items as i', 'i.id = r.output_product_id', 'left');
        $this->db->order_by('r.recipe_name', 'ASC');
        return $this->db->get()->result();
    }
}
