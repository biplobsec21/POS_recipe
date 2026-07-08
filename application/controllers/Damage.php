<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Damage extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load_global();
        $this->load->model('damage_model', 'damage');
    }

    // ---------------------------------------------------------------
    // LIST PAGE
    // ---------------------------------------------------------------

    public function index()
    {
        $this->permission_check('damage_view');
        $data = $this->data;
        $data['page_title'] = 'Damage Management';
        $this->load->view('damage-list', $data);
    }

    // ---------------------------------------------------------------
    // ADD / EDIT FORM
    // ---------------------------------------------------------------

    public function add()
    {
        $this->permission_check('damage_add');
        $data = $this->data;
        $data['page_title'] = 'Damage Entry';
        $data['damage_id']  = null;
        $this->load->view('damage', $data);
    }

    public function update($id)
    {
        $this->permission_check('damage_edit');
        $data = $this->data;
        $data['page_title'] = 'Edit Damage';
        $data['damage_id']  = (int) $id;
        $this->load->view('damage', $data);
    }

    // ---------------------------------------------------------------
    // SAVE / UPDATE  (mirrors sales/sales_save_and_update)
    // ---------------------------------------------------------------

    public function damage_save_and_update()
    {
        $this->form_validation->set_rules('damage_date', 'Damage Date', 'trim|required');

        if ($this->form_validation->run() === true) {
            $result = $this->damage->verify_save_and_update();
            echo $result;
        } else {
            echo 'Please fill all required (*) fields.';
        }
    }

    // ---------------------------------------------------------------
    // DATATABLES AJAX LIST
    // ---------------------------------------------------------------

    public function ajax_list()
    {
        $list = $this->damage->get_datatables();

        // Batch-fetch items for all displayed damage records
        $damage_ids = array();
        foreach ($list as $row) {
            $damage_ids[] = $row->id;
        }
        $items_map = $this->damage->get_damage_items_batch($damage_ids);

        $data = array();
        $no   = (int) $_POST['start'];

        foreach ($list as $row) {
            $no++;
            $r = array();

            $r[] = '<input type="checkbox" name="checkbox[]" value="' . $row->id . '" class="checkbox column_checkbox">';
            $r[] = show_date($row->damage_date);
            $r[] = $row->damage_code;
            $r[] = ucfirst($row->damage_type);
            $r[] = isset($items_map[$row->id]) ? $items_map[$row->id] : 'No Items';
            $r[] = $row->warehouse_name ?? '-';
            $r[] = app_number_format($row->total_qty);
            $r[] = app_number_format($row->total_value);

            // Status badge
            if ($row->status === 'approved') {
                $r[] = "<span class='label label-success'>Approved</span>";
            } else {
                $r[] = "<span class='label label-warning'>Draft</span>";
            }

            $r[] = ucfirst($row->created_by_name ?? $row->created_by ?? '-');

            // Action buttons
            $actions = '<div class="btn-group">
                            <a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" href="#">
                                Action <span class="caret"></span>
                            </a>
                            <ul role="menu" class="dropdown-menu dropdown-light pull-right">';

            if ($this->permissions('damage_view')) {
                $actions .= '<li>
                                <a href="damage/view/' . $row->id . '">
                                    <i class="fa fa-fw fa-eye text-blue"></i>View
                                </a>
                            </li>';
            }

            if ($this->permissions('damage_edit') && $row->status !== 'approved') {
                $actions .= '<li>
                                <a href="damage/update/' . $row->id . '">
                                    <i class="fa fa-fw fa-edit text-blue"></i>Edit
                                </a>
                            </li>';
            }

            if ($this->permissions('damage_approve') && $row->status !== 'approved') {
                $actions .= '<li>
                                <a style="cursor:pointer" onclick="approve_damage(' . $row->id . ')">
                                    <i class="fa fa-fw fa-check-circle text-green"></i>Approve
                                </a>
                            </li>';
            }

            if ($this->permissions('damage_add') || $this->permissions('damage_edit')) {
                $actions .= '<li>
                                <a target="_blank" href="damage/invoice/' . $row->id . '">
                                    <i class="fa fa-fw fa-print text-blue"></i>Print
                                </a>
                            </li>';
            }

            if ($this->permissions('damage_delete') && $row->status !== 'approved') {
                $actions .= '<li>
                                <a style="cursor:pointer" onclick="delete_damage(' . $row->id . ')">
                                    <i class="fa fa-fw fa-trash text-red"></i>Delete
                                </a>
                            </li>';
            }

            $actions .= '</ul></div>';
            $r[] = $actions;

            $data[] = $r;
        }

        $output = array(
            'draw'            => (int) $_POST['draw'],
            'recordsTotal'    => $this->damage->count_all(),
            'recordsFiltered' => $this->damage->count_filtered(),
            'data'            => $data,
        );

        echo json_encode($output);
    }

    // ---------------------------------------------------------------
    // ITEM SEARCH  (mirrors sales/search_item)
    // ---------------------------------------------------------------

    public function search_item()
    {
        $q      = $this->input->get('q');
        $result = $this->damage->search_item($q);
        echo $result;
    }

    // ---------------------------------------------------------------
    // ITEM ROW  (mirrors sales/return_row_with_data)
    // ---------------------------------------------------------------

    public function return_row_with_data($rowcount, $item_id)
    {
        echo $this->damage->get_items_info($rowcount, $item_id);
    }

    // ---------------------------------------------------------------
    // LOAD EXISTING ITEMS FOR EDIT  (mirrors sales/return_sales_list)
    // ---------------------------------------------------------------

    public function return_damage_list($damage_id)
    {
        echo $this->damage->return_damage_list($damage_id);
    }

    // ---------------------------------------------------------------
    // APPROVE
    // ---------------------------------------------------------------

    public function approve()
    {
        $this->permission_check_with_msg('damage_approve');
        $id = (int) $this->input->post('id');
        echo $this->damage->approve_damage($id);
    }

    // ---------------------------------------------------------------
    // DELETE SINGLE
    // ---------------------------------------------------------------

    public function delete_damage()
    {
        $this->permission_check_with_msg('damage_delete');
        $id = (int) $this->input->post('id');
        echo $this->damage->delete_damage($id);
    }

    // ---------------------------------------------------------------
    // DELETE MULTI
    // ---------------------------------------------------------------

    public function multi_delete()
    {
        $this->permission_check_with_msg('damage_delete');
        $ids = implode(',', array_map('intval', $_POST['checkbox']));
        echo $this->damage->multi_delete($ids);
    }

    // ---------------------------------------------------------------
    // VIEW (read-only detail page)
    // ---------------------------------------------------------------

    public function view($id)
    {
        $this->permission_check('damage_view');
        $data = $this->data;
        $damage = $this->damage->get_damage($id);
        if (empty($damage)) {
            show_404();
        }
        $data['page_title']   = 'Damage Details';
        $data['damage']       = $damage;
        $data['damage_items'] = $this->damage->get_damage_items($id);
        $this->load->view('damage-view', $data);
    }

    // ---------------------------------------------------------------
    // PRINT / INVOICE
    // ---------------------------------------------------------------

    public function invoice($id)
    {
        $this->permission_check('damage_view');
        $data = $this->data;
        $damage = $this->damage->get_damage($id);
        if (empty($damage)) {
            show_404();
        }
        $data['page_title']   = 'Damage Invoice';
        $data['damage']       = $damage;
        $data['damage_items'] = $this->damage->get_damage_items($id);
        $this->load->view('damage-invoice', $data);
    }
}
