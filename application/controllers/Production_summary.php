<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Production_summary extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load_global();
        $this->load->model('Production_summary_model');
    }

    /**
     * Main page - Production Summary Report
     */
    public function index()
    {
        $this->permission_check('production_view');
        
        $data = $this->data;
        $data['page_title'] = 'Production Summary Report';

        // Get dropdown data
        $data['recipes'] = $this->Production_summary_model->get_all_recipes();
        $data['materials'] = $this->Production_summary_model->get_all_materials();

        // Default date range (last 30 days)
        $data['from_date'] = date('Y-m-d', strtotime('-30 days'));
        $data['to_date'] = date('Y-m-d');

        // Get default data
        $data['material_summary'] = $this->Production_summary_model->get_material_summary(
            $data['from_date'],
            $data['to_date']
        );
        
        $data['output_summary'] = $this->Production_summary_model->get_output_summary(
            $data['from_date'],
            $data['to_date']
        );
        
        $data['summary_stats'] = $this->Production_summary_model->get_production_summary_stats(
            $data['from_date'],
            $data['to_date']
        );

        $this->load->view('production_summary/index', $data);
    }

    /**
     * AJAX endpoint: Get report data with filters
     */
    public function get_report_data()
    {
        if (!$this->input->is_ajax_request()) {
            show_error('Invalid request');
        }

        $this->permission_check('production_view');

        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $recipe_id = $this->input->post('recipe_id');
        $item_id = $this->input->post('item_id');

        // Validate dates
        if (!$this->_validate_date($from_date) || !$this->_validate_date($to_date)) {
            echo json_encode(array('success' => false, 'message' => 'Invalid date format'));
            return;
        }

        // Get all report data (only Approved batches)
        $material_summary = $this->Production_summary_model->get_material_summary(
            $from_date,
            $to_date,
            $recipe_id,
            $item_id
        );
        
        $output_summary = $this->Production_summary_model->get_output_summary(
            $from_date,
            $to_date,
            $recipe_id,
            $item_id
        );
        
        $summary_stats = $this->Production_summary_model->get_production_summary_stats(
            $from_date,
            $to_date,
            $recipe_id,
            $item_id
        );

        echo json_encode(array(
            'success' => true,
            'material_summary' => $material_summary,
            'output_summary' => $output_summary,
            'summary_stats' => $summary_stats
        ));
    }

    /**
     * Get recipe materials for a specific recipe (AJAX)
     */
    public function get_recipe_materials()
    {
        if (!$this->input->is_ajax_request()) {
            show_error('Invalid request');
        }

        $this->permission_check('production_view');

        $recipe_id = $this->input->post('recipe_id');

        if (empty($recipe_id) || !is_numeric($recipe_id)) {
            echo json_encode(array('success' => false, 'message' => 'Invalid recipe ID'));
            return;
        }

        // Get recipe materials
        $materials = $this->Production_summary_model->get_recipe_materials($recipe_id);

        echo json_encode(array(
            'success' => true,
            'materials' => $materials
        ));
    }

    /**
     * Get batch details for a specific material item (AJAX)
     */
    public function get_item_batch_details()
    {
        if (!$this->input->is_ajax_request()) {
            show_error('Invalid request');
        }

        $this->permission_check('production_view');

        $item_id = $this->input->post('item_id');
        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $recipe_id = $this->input->post('recipe_id');

        // Validate dates
        if (!$this->_validate_date($from_date) || !$this->_validate_date($to_date)) {
            echo json_encode(array('success' => false, 'message' => 'Invalid date format'));
            return;
        }

        if (empty($item_id) || !is_numeric($item_id)) {
            echo json_encode(array('success' => false, 'message' => 'Invalid item ID'));
            return;
        }

        // Get batch details with applied filters
        $batch_details = $this->Production_summary_model->get_item_batch_details(
            $item_id,
            $from_date,
            $to_date,
            $recipe_id
        );

        echo json_encode(array(
            'success' => true,
            'batch_details' => $batch_details
        ));
    }

    /**
     * Export report to CSV
     * Uses GET request to bypass CSRF
     */
    public function export_csv()
    {
        $this->permission_check('production_view');
        
        $from_date = $this->input->get('from_date') ?: $this->input->post('from_date');
        $to_date = $this->input->get('to_date') ?: $this->input->post('to_date');
        $recipe_id = $this->input->get('recipe_id') ?: $this->input->post('recipe_id');
        $item_id = $this->input->get('item_id') ?: $this->input->post('item_id');

        if (!$this->_validate_date($from_date) || !$this->_validate_date($to_date)) {
            echo "Invalid date format";
            return;
        }

        $material_summary = $this->Production_summary_model->get_material_summary(
            $from_date,
            $to_date,
            $recipe_id,
            $item_id
        );
        
        $output_summary = $this->Production_summary_model->get_output_summary(
            $from_date,
            $to_date,
            $recipe_id,
            $item_id
        );

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="production_summary_' . date('Y-m-d_H-i-s') . '.csv"');

        $output = fopen('php://output', 'w');

        // Add BOM for UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // ========== MATERIAL INPUT SECTION ==========
        fputcsv($output, array('MATERIAL INPUT SUMMARY'));
        fputcsv($output, array('From Date: ' . $from_date . ' | To Date: ' . $to_date));
        fputcsv($output, array(''));
        
        fputcsv($output, array(
            'Material Code',
            'Material Name',
            'Unit',
            'Total Used',
            'Total Batches'
        ));

        $material_total_used = 0;
        $material_total_batches = 0;
        foreach ($material_summary as $material) {
            fputcsv($output, array(
                $material->item_code,
                $material->item_name,
                $material->unit,
                number_format($material->total_used, 3),
                $material->total_batches
            ));
            $material_total_used += $material->total_used;
            $material_total_batches += $material->total_batches;
        }

        // Add totals footer for materials
        fputcsv($output, array(
            'TOTAL',
            '',
            '',
            number_format($material_total_used, 3),
            $material_total_batches
        ));

        fputcsv($output, array(''));
        fputcsv($output, array(''));

        // ========== OUTPUT PRODUCTS SECTION ==========
        fputcsv($output, array('OUTPUT PRODUCTS SUMMARY'));
        fputcsv($output, array('From Date: ' . $from_date . ' | To Date: ' . $to_date));
        fputcsv($output, array(''));
        
        fputcsv($output, array(
            'Recipe Name',
            'Output Product',
            'Unit',
            'Total Produced',
            'Average per Batch',
            'Total Batches',
            'Unique Materials Used'
        ));

        $output_total_produced = 0;
        $output_total_batches = 0;
        foreach ($output_summary as $prod) {
            fputcsv($output, array(
                $prod->recipe_name,
                $prod->output_product_name,
                $prod->output_unit,
                number_format($prod->total_produced, 2),
                number_format($prod->avg_per_batch, 2),
                $prod->total_batches,
                $prod->unique_items_used
            ));
            $output_total_produced += $prod->total_produced;
            $output_total_batches += $prod->total_batches;
        }

        // Add totals footer for output
        fputcsv($output, array(
            'TOTAL',
            '',
            '',
            number_format($output_total_produced, 2),
            '',
            $output_total_batches,
            ''
        ));

        fclose($output);
        exit;
    }

    /**
     * ========================
     * PRIVATE HELPER METHODS
     * ========================
     */

    /**
     * Validate date format (Y-m-d)
     */
    private function _validate_date($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
