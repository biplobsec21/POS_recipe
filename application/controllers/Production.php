<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Production extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Load necessary models
        $this->load_global();
        $this->load->model('Production_batch_model');
        $this->load->helper('sms_template_helper');
        $this->load->model('Inventory_movements_model');
        // Load additional models
        $this->load->model('Recipe_model');
        $this->load->model('Recipe_item_model');
        $this->load->model('Items_model');
    }

    public function index()
    {
        $this->permission_check('production_view');
        $data = $this->data;
        $data['page_title'] = 'Production Batches';
        $this->load->view('production/list', $data);
    }

    public function add()
    {
        // $this->permission_check('production_add');
        $data = $this->data;
        $data['page_title'] = 'Add Production Batch';
        $data['recipes'] = $this->Recipe_model->get_all();
        $this->load->view('production/add', $data);
    }

    public function save()
    {
        $this->form_validation->set_rules('recipe_id', 'Recipe', 'trim|required');
        $this->form_validation->set_rules('batch_quantity', 'Batch Quantity', 'trim|required|numeric');

        if ($this->form_validation->run() == FALSE) {
            echo "Please fill all the required fields.";
            return;
        }

        $this->db->trans_begin();

        $recipe_id = $this->input->post('recipe_id');
        $batch_quantity = $this->input->post('batch_quantity');
        $notes = $this->input->post('notes');

        // Generate a unique batch code
        $batch_code = 'PROD-' . date('YmdHis');

        $production_batch_data = array(
            'batch_code' => $batch_code,
            'recipe_id' => $recipe_id,
            'batch_quantity' => $batch_quantity,
            'notes' => $notes,
            'status' => 'Draft',
            'warehouse_id' => 1,
            'total_cost' => 0,
            'cost_per_unit' => 0,
            'created_by' => $this->session->userdata('inv_userid'),
            'created_at' => date('Y-m-d H:i:s'),
        );

        $production_batch_id = $this->Production_batch_model->insert($production_batch_data);

        if ($production_batch_id) {
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                echo "Failed to save production batch. Please try again.";
            } else {
                $this->db->trans_commit();
                echo "success";
            }
        } else {
            $this->db->trans_rollback();
            echo "Failed to save production batch. Please try again.";
        }
    }

    public function approve($id)
    {
        $this->db->trans_begin();

        try {
            $production_batch = $this->Production_batch_model->get_by_id($id);

            if (!$production_batch) {
                throw new Exception("Production batch not found.");
            }

            // Check if already approved
            if ($production_batch->status == 'Approved') {
                throw new Exception("Production batch is already approved.");
            }

            $recipe = $this->Recipe_model->get_by_id($production_batch->recipe_id);
            if (!$recipe) {
                throw new Exception("Recipe not found.");
            }

            $recipe_items = $this->Recipe_item_model->get_by_recipe_id($production_batch->recipe_id);
            if (empty($recipe_items)) {
                throw new Exception("No ingredients found for this recipe.");
            }

            // Check if there is enough stock for the ingredients
            $stock_issues = [];
            foreach ($recipe_items as $item) {
                $item_details = $this->Items_model->get_item_details_by_id($item->item_id);
                if (!$item_details) {
                    $stock_issues[] = "Item not found: ID " . $item->item_id;
                    continue;
                }

                $required_qty = $item->quantity * $production_batch->batch_quantity;

                if ($item_details->stock < $required_qty) {
                    $stock_issues[] = "Not enough stock for " . $item_details->item_name .
                        " (Required: " . $required_qty . ", Available: " . $item_details->stock . ")";
                }
            }

            if (!empty($stock_issues)) {
                throw new Exception(implode("; ", $stock_issues));
            }

            $total_cost = 0;

            // Adjust stock levels and calculate cost - USING PROPER STOCK ENTRY METHOD
            foreach ($recipe_items as $item) {
                $item_details = $this->Items_model->get_item_details_by_id($item->item_id);
                $consumed_qty = $item->quantity * $production_batch->batch_quantity;
                $total_cost += $item_details->purchase_price * $consumed_qty;

                // Use the proper stock_entry method instead of direct update
                $stock_result = $this->Items_model->stock_entry(
                    date('Y-m-d H:i:s'),
                    $item->item_id,
                    -$consumed_qty, // Negative for consumption
                    'Production Consumption - Batch: ' . $production_batch->batch_code
                );

                if (!$stock_result) {
                    throw new Exception("Failed to update stock for " . $item_details->item_name);
                }

                // UPDATE items quantity in items table
                $this->load->model('pos_model');
                $update_qty_result = $this->pos_model->update_items_quantity($item->item_id);
                if (!$update_qty_result) {
                    throw new Exception("Failed to update item quantity for " . $item_details->item_name);
                }

                // Log inventory movement for consumption
                $movement_id = $this->Inventory_movements_model->insert(array(
                    'item_id' => $item->item_id,
                    'qty' => -$consumed_qty,
                    'type' => 'PRODUCTION_CONSUME',
                    'reference_id' => $id,
                    'created_by' => $this->session->userdata('inv_userid'),
                    'created_at' => date('Y-m-d H:i:s'),
                ));

                if (!$movement_id) {
                    throw new Exception("Failed to log inventory movement for " . $item_details->item_name);
                }
            }

            // Calculate cost per unit with validation
            $total_output_qty = $recipe->yield_quantity * $production_batch->batch_quantity;

            if ($total_output_qty <= 0) {
                throw new Exception("Invalid yield quantity. Cannot produce zero or negative quantity.");
            }

            if ($total_cost < 0) {
                throw new Exception("Invalid total cost calculated.");
            }

            $cost_per_unit = $total_cost / $total_output_qty;

            // Increase output product stock - USING PROPER STOCK ENTRY METHOD
            $output_item = $this->Items_model->get_item_details_by_id($recipe->output_product_id);
            if (!$output_item) {
                throw new Exception("Output product not found.");
            }

            // Use proper stock_entry method for output
            $output_stock_result = $this->Items_model->stock_entry(
                date('Y-m-d H:i:s'),
                $recipe->output_product_id,
                $total_output_qty, // Positive for production output
                'Production Output - Batch: ' . $production_batch->batch_code
            );

            if (!$output_stock_result) {
                throw new Exception("Failed to update output product stock.");
            }

            // UPDATE items quantity in items table for output product
            $this->load->model('pos_model');
            $update_output_qty_result = $this->pos_model->update_items_quantity($recipe->output_product_id);
            if (!$update_output_qty_result) {
                throw new Exception("Failed to update output product quantity.");
            }

            // Log inventory movement for output
            $output_movement_id = $this->Inventory_movements_model->insert(array(
                'item_id' => $recipe->output_product_id,
                'qty' => $total_output_qty,
                'type' => 'PRODUCTION_OUTPUT',
                'reference_id' => $id,
                'created_by' => $this->session->userdata('inv_userid'),
                'created_at' => date('Y-m-d H:i:s'),
            ));

            if (!$output_movement_id) {
                throw new Exception("Failed to log output inventory movement.");
            }

            // Update production batch with cost and approval info
            $production_batch_update_data = array(
                'status' => 'Approved',
                'total_cost' => $total_cost,
                'cost_per_unit' => $cost_per_unit,
                'approved_by' => $this->session->userdata('inv_userid'),
                'approved_at' => date('Y-m-d H:i:s'),
            );

            $update_result = $this->Production_batch_model->update($id, $production_batch_update_data);

            if (!$update_result) {
                throw new Exception("Failed to update production batch status.");
            }

            // Update the cost price of the finished product
            $this->db->set('purchase_price', $cost_per_unit);
            $this->db->where('id', $recipe->output_product_id);
            $this->db->update('db_items');

            if ($this->db->affected_rows() === 0) {
                // This might not be an error if the price is the same, so we'll just log it
                log_message('info', 'Purchase price unchanged for output product ID: ' . $recipe->output_product_id);
            }

            // Final transaction check
            if ($this->db->trans_status() === FALSE) {
                throw new Exception("Database transaction failed.");
            }

            $this->db->trans_commit();
            echo "success";
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo $e->getMessage();
        }
    }

    public function get_recipe_details($recipe_id)
    {
        $recipe = $this->Recipe_model->get_by_id($recipe_id);
        $recipe_items = $this->Recipe_item_model->get_by_recipe_id($recipe_id);

        $ingredients = array();
        foreach ($recipe_items as $item) {
            $item_details = $this->Items_model->get_item_details_by_id($item->item_id);
            $ingredients[] = array(
                'item_name' => $item_details->item_name,
                'required_qty' => $item->quantity,
                'available_qty' => $item_details->stock,
                'unit' => $item_details->unit_name,
            );
        }

        $data = array(
            'yield_quantity' => $recipe->yield_quantity,
            'ingredients' => $ingredients,
        );

        echo json_encode($data);
    }

    // Add these methods to your existing Production controller

    public function edit($id)
    {
        // $this->permission_check('production_edit');
        $data = $this->data;
        $data['page_title'] = 'Edit Production Batch';

        // Get production batch details
        $production_batch = $this->Production_batch_model->get_by_id($id);
        if (!$production_batch) {
            show_404();
        }

        // Check if production batch can be edited (only Draft status can be edited)
        if ($production_batch->status != 'Draft') {
            $this->session->set_flashdata('error', 'Only Draft production batches can be edited.');
            redirect('production');
        }

        $data['production'] = $production_batch;
        $data['recipes'] = $this->Recipe_model->get_all();

        $this->load->view('production/edit', $data);
    }

    public function update($id)
    {
        $this->form_validation->set_rules('recipe_id', 'Recipe', 'trim|required');
        $this->form_validation->set_rules('batch_quantity', 'Batch Quantity', 'trim|required|numeric');

        if ($this->form_validation->run() == FALSE) {
            echo "Please fill all the required fields.";
            return;
        }

        // Check if production batch exists and is in Draft status
        $production_batch = $this->Production_batch_model->get_by_id($id);
        if (!$production_batch || $production_batch->status != 'Draft') {
            echo "Production batch not found or cannot be edited.";
            return;
        }

        $this->db->trans_begin();

        $recipe_id = $this->input->post('recipe_id');
        $batch_quantity = $this->input->post('batch_quantity');
        $notes = $this->input->post('notes');

        $production_batch_data = array(
            'recipe_id' => $recipe_id,
            'batch_quantity' => $batch_quantity,
            'notes' => $notes,
            'updated_by' => $this->session->userdata('inv_userid'),
            'updated_at' => date('Y-m-d H:i:s'),
        );

        $result = $this->Production_batch_model->update($id, $production_batch_data);

        if ($result) {
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                echo "Failed to update production batch. Please try again.";
            } else {
                $this->db->trans_commit();
                echo "success";
            }
        } else {
            $this->db->trans_rollback();
            echo "Failed to update production batch. Please try again.";
        }
    }

    public function delete($id)
    {
        $this->db->trans_begin();

        try {
            $production_batch = $this->Production_batch_model->get_by_id($id);
            if (!$production_batch) {
                throw new Exception("Production batch not found.");
            }

            if ($production_batch->status == 'Approved') {
                $recipe = $this->Recipe_model->get_by_id($production_batch->recipe_id);
                if (!$recipe) {
                    throw new Exception("Recipe not found.");
                }

                $recipe_items = $this->Recipe_item_model->get_by_recipe_id($production_batch->recipe_id);

                // Revert stock adjustments for ingredients - USING PROPER STOCK ENTRY METHOD
                foreach ($recipe_items as $item) {
                    $consumed_qty = $item->quantity * $production_batch->batch_quantity;

                    // Add back the consumed stock
                    $stock_result = $this->Items_model->stock_entry(
                        date('Y-m-d H:i:s'),
                        $item->item_id,
                        $consumed_qty, // Positive to add back
                        'Production Revert - Batch: ' . $production_batch->batch_code
                    );

                    if (!$stock_result) {
                        throw new Exception("Failed to revert stock for item ID: " . $item->item_id);
                    }

                    // UPDATE items quantity in items table
                    $this->load->model('pos_model');
                    $update_qty_result = $this->pos_model->update_items_quantity($item->item_id);
                    if (!$update_qty_result) {
                        throw new Exception("Failed to update item quantity during revert.");
                    }
                }

                // Remove output product - USING PROPER STOCK ENTRY METHOD
                $output_qty = $recipe->yield_quantity * $production_batch->batch_quantity;
                $output_stock_result = $this->Items_model->stock_entry(
                    date('Y-m-d H:i:s'),
                    $recipe->output_product_id,
                    -$output_qty, // Negative to remove
                    'Production Revert - Batch: ' . $production_batch->batch_code
                );

                if (!$output_stock_result) {
                    throw new Exception("Failed to revert output product stock.");
                }

                // UPDATE items quantity in items table for output product
                $this->load->model('pos_model');
                $update_output_qty_result = $this->pos_model->update_items_quantity($recipe->output_product_id);
                if (!$update_output_qty_result) {
                    throw new Exception("Failed to update output product quantity during revert.");
                }
            }

            // Delete the production batch
            $delete_result = $this->Production_batch_model->delete($id);
            if (!$delete_result) {
                throw new Exception("Failed to delete production batch.");
            }

            if ($this->db->trans_status() === FALSE) {
                throw new Exception("Database transaction failed.");
            }

            $this->db->trans_commit();
            echo "success";
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo $e->getMessage();
        }
    }

    public function ajax_list()
    {
        // Use Production_batch_model datatables methods
        $list = $this->Production_batch_model->get_datatables();

        $data = array();
        $no = $_POST['start'];
        foreach ($list as $production) {
            $row = array();
            $row[] = '<input type="checkbox" name="checkbox[]" value="' . $production->id . '" class="checkbox column_checkbox" >';
            $row[] = $production->batch_code;
            $row[] = $production->recipe_name;
            $row[] = number_format($production->batch_quantity, 2);

            // Recipe Details Column
            $recipe_details = '<strong>Output:</strong> ' . ($production->output_product_name ?? 'N/A') . '<br>';
            $recipe_details .= '<strong>Yield per Batch:</strong> ' . number_format(($production->yield_quantity ?? 0), 2) . '<br>';
            $recipe_details .= '<strong>Total Output:</strong> ' . number_format(($production->total_output_qty ?? 0), 2);
            $row[] = $recipe_details;

            // Cost Details Column
            $cost_details = '';
            if ($production->status == 'Approved') {
                $cost_details .= '<strong>Total Cost:</strong> ' . ($this->data['currency'] ?? '') . number_format($production->total_cost, 2) . '<br>';
                $cost_details .= '<strong>Cost/Unit:</strong> ' . ($this->data['currency'] ?? '') . number_format($production->cost_per_unit, 2);
            } else {
                $cost_details .= '<em>Not calculated</em>';
            }
            $row[] = $cost_details;

            $row[] = show_date($production->created_at);
            $row[] = '<span class="label label-' . ($production->status == 'Approved' ? 'success' : ($production->status == 'Draft' ? 'warning' : 'default')) . '">' . $production->status . '</span>';
            $row[] = $this->_get_username($production->created_by);

            // Action buttons
            $buttons = '';
            if ($this->permissions('production_edit') && $production->status == 'Draft') {
                $buttons .= '<a class="btn btn-primary btn-sm" href="' . base_url('production/edit/' . $production->id) . '" title="Edit"><i class="fa fa-edit"></i></a>';
            }
            if ($this->permissions('production_delete') && $production->status != 'Approved') {
                $buttons .= ' <a class="btn btn-danger btn-sm" href="javascript:void(0)" title="Delete" onclick="delete_production(' . $production->id . ')"><i class="fa fa-trash"></i></a>';
            }
            if ($production->status == 'Draft' && $this->permissions('production_approve')) {
                $buttons .= ' <a class="btn btn-success btn-sm" href="javascript:void(0)" title="Approve" onclick="approve_production(' . $production->id . ')"><i class="fa fa-check"></i></a>';
            }

            // View Details Button
            $buttons .= ' <a class="btn btn-info btn-sm" href="javascript:void(0)" title="View Details" onclick="view_production_details(' . $production->id . ')"><i class="fa fa-eye"></i></a>';

            $row[] = $buttons;

            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->Production_batch_model->count_all(),
            "recordsFiltered" => $this->Production_batch_model->count_filtered(),
            "data" => $data,
        );

        echo json_encode($output);
    }
    private function _get_username($user_id)
    {
        if (empty($user_id)) {
            return 'System';
        }

        $query = $this->db->select('username')->where('id', $user_id)->get('db_users');
        if ($query->num_rows() > 0) {
            return $query->row()->username;
        }

        return 'Unknown';
    }
    public function view($id)
    {
        $this->permission_check('production_view');
        $data = $this->data;
        $data['page_title'] = 'Production Batch Details';

        // Get production batch details with all related information
        $production_batch = $this->Production_batch_model->get_by_id($id);
        if (!$production_batch) {
            show_404();
        }

        // Get recipe items
        $data['recipe_items'] = $this->Recipe_item_model->get_by_recipe_id($production_batch->recipe_id);

        // Get inventory movements for this batch
        $data['inventory_movements'] = $this->Inventory_movements_model->get_by_reference('PRODUCTION_CONSUME', $id);
        $data['output_movements'] = $this->Inventory_movements_model->get_by_reference('PRODUCTION_OUTPUT', $id);

        $data['production'] = $production_batch;
        $data['CI'] = $this; // Make controller instance available in view
        $data['created_by_username'] = $this->_get_username($production_batch->created_by);
        $data['approved_by_username'] = $production_batch->approved_by ? $this->_get_username($production_batch->approved_by) : 'N/A';
        $this->load->view('production/view', $data);
    }
}
