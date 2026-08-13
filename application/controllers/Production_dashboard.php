<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Production_dashboard extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load_global();
        $this->load->model('Production_dashboard_model');
        $this->load->model('Production_batch_model');
        $this->load->model('Recipe_model');
        $this->load->model('Items_model');
    }

    /**
     * Main dashboard page with all 4 tabs
     */
    public function index()
    {
        $this->permission_check('production_view');
        
        $data = $this->data;
        $data['page_title'] = 'Production Dashboard';

        // Get quick stats for top cards
        $data['today_count'] = $this->Production_dashboard_model->get_today_count();
        $data['today_approved'] = $this->Production_dashboard_model->get_today_approved_count();
        $data['total_count'] = $this->Production_dashboard_model->get_total_count();
        $data['total_output'] = $this->Production_dashboard_model->get_total_output_quantity();
        $data['total_cost'] = $this->Production_dashboard_model->get_total_cost();

        // Get dropdown data
        $data['recipes'] = $this->Production_dashboard_model->get_all_recipes();
        $data['items'] = $this->Production_dashboard_model->get_all_production_items();

        // Default date range (last 30 days)
        $data['from_date'] = date('Y-m-d', strtotime('-30 days'));
        $data['to_date'] = date('Y-m-d');

        // Get default data for date range tab
        $filters = array();
        $data['date_range_productions'] = $this->Production_dashboard_model->get_by_date_range(
            $data['from_date'],
            $data['to_date'],
            $filters
        );
        $data['date_range_summary'] = $this->Production_dashboard_model->get_date_range_summary(
            $data['from_date'],
            $data['to_date'],
            $filters
        );

        // Get today's productions
        $data['today_productions'] = $this->Production_dashboard_model->get_today_productions();

        // Get total summary
        $data['total_summary'] = $this->Production_dashboard_model->get_total_summary();

        $this->load->view('production_dashboard/index', $data);
    }

    /**
     * AJAX endpoint: Get productions by date range with filters
     */
    public function get_date_range_productions()
    {
        if (!$this->input->is_ajax_request()) {
            show_error('Invalid request');
        }

        $this->permission_check('production_view');

        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $status = $this->input->post('status');
        $recipe_id = $this->input->post('recipe_id');

        // Validate dates
        if (!$this->_validate_date($from_date) || !$this->_validate_date($to_date)) {
            echo json_encode(array('success' => false, 'message' => 'Invalid date format'));
            return;
        }

        $filters = array();
        if ($status) {
            $filters['status'] = $status;
        }
        if ($recipe_id) {
            $filters['recipe_id'] = $recipe_id;
        }

        $productions = $this->Production_dashboard_model->get_by_date_range($from_date, $to_date, $filters);
        $summary = $this->Production_dashboard_model->get_date_range_summary($from_date, $to_date, $filters);

        echo json_encode(array(
            'success' => true,
            'productions' => $productions,
            'summary' => $summary
        ));
    }

    /**
     * AJAX endpoint: Get item usage report
     */
    public function get_item_usage()
    {
        if (!$this->input->is_ajax_request()) {
            show_error('Invalid request');
        }

        $this->permission_check('production_view');

        $item_id = $this->input->post('item_id');
        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $status = $this->input->post('status');

        // Validate inputs
        if (!$item_id || !$this->_validate_date($from_date) || !$this->_validate_date($to_date)) {
            echo json_encode(array('success' => false, 'message' => 'Invalid input'));
            return;
        }

        $usage_report = $this->Production_dashboard_model->get_item_usage_report($item_id, $from_date, $to_date, $status);
        $usage_summary = $this->Production_dashboard_model->get_item_usage_summary($item_id, $from_date, $to_date, $status);

        echo json_encode(array(
            'success' => true,
            'report' => $usage_report,
            'summary' => $usage_summary
        ));
    }

    /**
     * AJAX endpoint: Export productions to CSV
     */
    public function export_productions()
    {
        $this->permission_check('production_view');

        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $status = $this->input->post('status');
        $recipe_id = $this->input->post('recipe_id');

        if (!$this->_validate_date($from_date) || !$this->_validate_date($to_date)) {
            echo "Invalid date format";
            return;
        }

        $filters = array();
        if ($status) {
            $filters['status'] = $status;
        }
        if ($recipe_id) {
            $filters['recipe_id'] = $recipe_id;
        }

        $productions = $this->Production_dashboard_model->get_by_date_range($from_date, $to_date, $filters);

        if (empty($productions)) {
            echo "No data to export";
            return;
        }

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="productions_' . date('Y-m-d_H-i-s') . '.csv"');

        $output = fopen('php://output', 'w');

        // Add BOM for UTF-8 to display special characters properly in Excel
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Write headers
        fputcsv($output, array(
            'Batch Code',
            'Recipe Name',
            'Output Product',
            'Batch Quantity',
            'Total Output',
            'Status',
            'Total Cost',
            'Cost Per Unit',
            'Created Date',
            'Created By'
        ));

        // Write data rows
        foreach ($productions as $prod) {
            fputcsv($output, array(
                $prod->batch_code,
                $prod->recipe_name,
                $prod->output_product_name,
                $prod->batch_quantity,
                $prod->total_output_qty,
                $prod->status,
                $prod->total_cost,
                $prod->cost_per_unit,
                $prod->created_at,
                $prod->created_by_name
            ));
        }

        fclose($output);
        exit;
    }

    /**
     * AJAX endpoint: Export item usage to CSV
     */
    public function export_item_usage()
    {
        $this->permission_check('production_view');

        $item_id = $this->input->post('item_id');
        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $status = $this->input->post('status');

        if (!$item_id || !$this->_validate_date($from_date) || !$this->_validate_date($to_date)) {
            echo "Invalid input";
            return;
        }

        $usage_report = $this->Production_dashboard_model->get_item_usage_report($item_id, $from_date, $to_date, $status);

        if (empty($usage_report)) {
            echo "No data to export";
            return;
        }

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="item_usage_' . date('Y-m-d_H-i-s') . '.csv"');

        $output = fopen('php://output', 'w');

        // Add BOM for UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Write headers
        fputcsv($output, array(
            'Batch Code',
            'Recipe Name',
            'Batch Quantity',
            'Qty Per Batch',
            'Total Consumed',
            'Status',
            'Created Date',
            'Created By'
        ));

        // Write data rows
        foreach ($usage_report as $item) {
            fputcsv($output, array(
                $item->batch_code,
                $item->recipe_name,
                $item->batch_quantity,
                $item->quantity_per_batch,
                $item->total_consumed,
                $item->status,
                $item->created_at,
                $item->created_by_name
            ));
        }

        fclose($output);
        exit;
    }

    /**
     * AJAX endpoint: Get items for dropdown (used in filters)
     */
    public function get_items_json()
    {
        if (!$this->input->is_ajax_request()) {
            show_error('Invalid request');
        }

        $this->permission_check('production_view');

        $search = $this->input->get('search');
        
        // Get production items with optional search
        $query = $this->db->select('i.id, i.item_code, i.item_name')
            ->from('db_items as i')
            ->join('recipe_items as ri', 'ri.item_id = i.id', 'inner')
            ->distinct();
            
        if (!empty($search)) {
            $query->where('(i.item_code LIKE "%' . $search . '%" OR i.item_name LIKE "%' . $search . '%")');
        }
        
        $query->order_by('i.item_name', 'ASC')
            ->limit(50);
            
        $results = $query->get()->result();
        
        $items = array();
        foreach ($results as $item) {
            $items[] = array(
                'id' => $item->id,
                'text' => $item->item_code . ' - ' . $item->item_name
            );
        }
        
        echo json_encode(array('results' => $items));
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
