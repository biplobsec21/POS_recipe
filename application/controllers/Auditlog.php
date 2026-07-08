<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auditlog extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load_global();
        // audit_log model is already autoloaded globally
    }

    // ---------------------------------------------------------------
    // LIST PAGE
    // ---------------------------------------------------------------
    public function index()
    {
        $this->permission_check('audit_log_view');
        $data = $this->data;
        $data['page_title'] = 'Audit Log';

        // Populate user filter dropdown
        $data['users'] = $this->db->select('id, username')
                                  ->from('db_users')
                                  ->where('status', 1)
                                  ->order_by('username', 'asc')
                                  ->get()->result();

        $this->load->view('audit-log-list', $data);
    }

    // ---------------------------------------------------------------
    // DATATABLES AJAX
    // ---------------------------------------------------------------
    public function ajax_list()
    {
        $this->permission_check('audit_log_view');

        $list = $this->audit_log->get_datatables();
        $data = [];
        $no   = (int) $_POST['start'];

        foreach ($list as $row) {
            $no++;

            // Action badge colour
            $badge = [
                'create' => 'label-success',
                'update' => 'label-warning',
                'delete' => 'label-danger',
            ];
            $badge_class = isset($badge[$row->action]) ? $badge[$row->action] : 'label-default';
            $action_badge = "<span class='label {$badge_class}'>" . ucfirst($row->action) . "</span>";

            // Changed fields summary
            $fields_html = '-';
            if (!empty($row->changed_fields)) {
                $fields = json_decode($row->changed_fields, true);
                if (is_array($fields)) {
                    $count = count($fields);
                    $preview = implode(', ', array_slice($fields, 0, 3));
                    $fields_html = "<span title='" . htmlspecialchars(implode(', ', $fields)) . "'>"
                                 . $count . ' field' . ($count !== 1 ? 's' : '')
                                 . ($count > 3 ? " <small class='text-muted'>({$preview}…)</small>" : " <small class='text-muted'>({$preview})</small>")
                                 . "</span>";
                }
            }

            $r   = [];
            $r[] = $no;
            $r[] = "<span class='label label-default'>" . ucfirst($row->module) . "</span>";
            $r[] = $action_badge;
            $r[] = "<code>" . htmlspecialchars($row->record_code ?? $row->record_id) . "</code>";
            $r[] = $fields_html;
            $r[] = ucfirst($row->username ?? '-');
            $r[] = $row->ip_address;
            $r[] = date('d-m-Y H:i:s', strtotime($row->created_at));
            $r[] = "<a href='auditlog/detail/{$row->id}' class='btn btn-xs btn-primary' title='View Diff'>
                        <i class='fa fa-search'></i> Detail
                    </a>";

            $data[] = $r;
        }

        echo json_encode([
            'draw'            => (int) $_POST['draw'],
            'recordsTotal'    => $this->audit_log->count_all(),
            'recordsFiltered' => $this->audit_log->count_filtered(),
            'data'            => $data,
        ]);
    }

    // ---------------------------------------------------------------
    // DETAIL / DIFF VIEW
    // ---------------------------------------------------------------
    public function detail($id)
    {
        $this->permission_check('audit_log_view');
        $data = $this->data;

        $log = $this->audit_log->get_by_id($id);
        if (empty($log)) {
            show_404();
        }

        $data['log']        = $log;
        $data['diff']       = $this->audit_log->build_diff($log->old_values, $log->new_values);
        $data['history']    = $this->audit_log->get_record_history($log->module, $log->record_id);
        $data['page_title'] = 'Audit Log — Detail';

        $this->load->view('audit-log-detail', $data);
    }

    // ---------------------------------------------------------------
    // EXPORT CSV
    // ---------------------------------------------------------------
    public function export()
    {
        $this->permission_check('audit_log_export');

        $filters = [
            'module'    => $this->input->get('module'),
            'action'    => $this->input->get('action'),
            'username'  => $this->input->get('username'),
            'from_date' => $this->input->get('from_date'),
            'to_date'   => $this->input->get('to_date'),
        ];

        $rows = $this->audit_log->get_export_data($filters);

        $filename = 'audit_log_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');

        // Header row
        fputcsv($out, [
            'Module', 'Action', 'Record ID', 'Record Code',
            'Changed Fields', 'Old Values', 'New Values',
            'Username', 'IP Address', 'User Agent', 'Date/Time',
        ]);

        foreach ($rows as $row) {
            fputcsv($out, [
                $row->module,
                $row->action,
                $row->record_id,
                $row->record_code,
                $row->changed_fields,
                $row->old_values,
                $row->new_values,
                $row->username,
                $row->ip_address,
                $row->user_agent,
                $row->created_at,
            ]);
        }

        fclose($out);
        exit;
    }
}
