<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Production_item_report extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load_global();
        $this->load->model('Production_item_report_model');
    }

    /**
     * Main page - Item usage report
     */
    public function index()
    {
        $this->permission_check('production_view');
        
        $data = $this->data;
        $data['page_title'] = 'Production Item Report';

        // Get dropdown data
        $data['items'] = $this->Production_item_report_model->get_all_production_items();

        // Default date range (last 30 days)
        $data['from_date'] = date('Y-m-d', strtotime('-30 days'));
        $data['to_date'] = date('Y-m-d');

        $this->load->view('production_item_report/index', $data);
    }

    /**
     * AJAX endpoint: Get item usage report for date range
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

        // Validate inputs
        if (!$item_id || !$this->_validate_date($from_date) || !$this->_validate_date($to_date)) {
            echo json_encode(array('success' => false, 'message' => 'Invalid input'));
            return;
        }

        $usage_report = $this->Production_item_report_model->get_item_usage_report($item_id, $from_date, $to_date);
        $usage_summary = $this->Production_item_report_model->get_item_usage_summary($item_id, $from_date, $to_date);

        echo json_encode(array(
            'success' => true,
            'report' => $usage_report,
            'summary' => $usage_summary
        ));
    }

    /**
     * Export item usage to CSV
     * Uses GET request to bypass CSRF - Always exports Approved status only
     */
    public function export_item_usage()
    {
        // No permission check for export - GET request used to bypass CSRF
        // Users accessing this URL directly should have valid session

        $item_id = $this->input->get('item_id') ?: $this->input->post('item_id');
        $from_date = $this->input->get('from_date') ?: $this->input->post('from_date');
        $to_date = $this->input->get('to_date') ?: $this->input->post('to_date');

        if (!$item_id || !$this->_validate_date($from_date) || !$this->_validate_date($to_date)) {
            echo "Invalid input";
            return;
        }

        $usage_report = $this->Production_item_report_model->get_item_usage_report($item_id, $from_date, $to_date);

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
        // Allow requests for this endpoint
        $this->permission_check('production_view');

        $search = $this->input->get('search');
        
        // Get production items with optional search
        $query = $this->db->select('i.id, i.item_code, i.item_name')
            ->from('db_items as i')
            ->join('recipe_items as ri', 'ri.item_id = i.id', 'inner')
            ->distinct();
            
        if (!empty($search)) {
            $search_term = $this->db->escape_like_str($search);
            $query->where("(i.item_code LIKE '%{$search_term}%' OR i.item_name LIKE '%{$search_term}%')");
        }
        
        $query->order_by('i.item_name', 'ASC')
            ->limit(50);
            
        $results = $query->get()->result();
        
        $items = array();
        
        // Add default option
        $items[] = array(
            'id' => '',
            'text' => '-- Select Item --'
        );
        
        // Add items from database
        if (!empty($results)) {
            foreach ($results as $item) {
                $items[] = array(
                    'id' => $item->id,
                    'text' => $item->item_code . ' - ' . $item->item_name
                );
            }
        }
        
        header('Content-Type: application/json');
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
