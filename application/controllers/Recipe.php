<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Recipe extends MY_Controller
{ // Assuming MY_Controller exists and handles common functionalities

    public function __construct()
    {
        parent::__construct();
        // try to load global data prepared by MY_Controller, fallback to empty array
        if (method_exists($this, 'load_global')) {
            $this->load_global();
        } else {
            $this->data = array();
        }
        // Load necessary models
        $this->load->model('Recipe_model');
        $this->load->model('Recipe_item_model');
        $this->load->model('Items_model'); // To select output products and ingredients
        $this->load->model('login_model'); // For permissions if MY_Controller doesn't handle it
        // Load Auth model only if the file exists (avoid Loader fatal on missing model)
        $authPath1 = APPPATH . 'models/Auth_model.php';
        $authPath2 = APPPATH . 'models/auth_model.php';
        if (is_file($authPath1)) {
            $this->load->model('Auth_model');
        } elseif (is_file($authPath2)) {
            $this->load->model('auth_model');
        }
    }

    public function index()
    {
        // ensure global data is passed to the view
        $data = $this->data;
        $data['page_title'] = 'Recipes List';
        $this->load->view('recipe/list', $data);
    }

    public function add()
    {
        $data = $this->data;
        // Display form to add new recipe
        $data['page_title'] = 'Add Recipe';
        // Load necessary data for dropdowns (e.g., all items for output product and ingredients)
        $data['all_items'] = $this->Items_model->get_all_items(); // Assuming this method exists
        $this->load->view('recipe/add', $data);
    }

    public function save()
    {
        $this->form_validation->set_rules('recipe_name', 'Recipe Name', 'trim|required');
        $this->form_validation->set_rules('output_product_id', 'Output Product', 'trim|required');
        $this->form_validation->set_rules('yield_quantity', 'Yield Quantity', 'trim|required|numeric');

        if ($this->form_validation->run() == FALSE) {
            echo "Please fill all the required fields.";
            return;
        }

        $recipe_data = array(
            'recipe_name' => $this->input->post('recipe_name'),
            'output_product_id' => $this->input->post('output_product_id'),
            'yield_quantity' => $this->input->post('yield_quantity'),
            'notes' => $this->input->post('notes'),
            'created_by' => $this->session->userdata('inv_userid'),
            'created_at' => date('Y-m-d H:i:s'),
        );

        $this->db->trans_begin();
        $recipe_id = $this->Recipe_model->insert($recipe_data);

        if ($recipe_id) {
            $item_ids = $this->input->post('item_id');
            $quantities = $this->input->post('quantity');
            $units = $this->input->post('unit');

            if (!empty($item_ids)) {
                for ($i = 0; $i < count($item_ids); $i++) {
                    $recipe_item_data = array(
                        'recipe_id' => $recipe_id,
                        'item_id' => $item_ids[$i],
                        'quantity' => $quantities[$i],
                        'unit' => $units[$i],
                    );
                    $this->Recipe_item_model->insert($recipe_item_data);
                }
            }

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                echo "Failed to save recipe. Please try again.";
            } else {
                $this->db->trans_commit();
                echo "success";
            }
        } else {
            $this->db->trans_rollback();
            echo "Failed to save recipe. Please try again.";
        }
    }

    public function edit($id)
    {
        $data = $this->data;
        // Display form to edit existing recipe
        $data['page_title'] = 'Edit Recipe';
        $data['recipe'] = $this->Recipe_model->get_by_id($id);
        $data['recipe_items'] = $this->Recipe_item_model->get_by_recipe_id($id);
        $data['all_items'] = $this->Items_model->get_all_items();
        $this->load->view('recipe/edit', $data);
    }

    public function update()
    {
        $this->form_validation->set_rules('recipe_name', 'Recipe Name', 'trim|required');
        $this->form_validation->set_rules('output_product_id', 'Output Product', 'trim|required');
        $this->form_validation->set_rules('yield_quantity', 'Yield Quantity', 'trim|required|numeric');

        if ($this->form_validation->run() == FALSE) {
            echo "Please fill all the required fields.";
            return;
        }

        $recipe_id = $this->input->post('recipe_id');
        $recipe_data = array(
            'recipe_name' => $this->input->post('recipe_name'),
            'output_product_id' => $this->input->post('output_product_id'),
            'yield_quantity' => $this->input->post('yield_quantity'),
            'notes' => $this->input->post('notes'),
        );

        $this->db->trans_begin();
        $this->Recipe_model->update($recipe_id, $recipe_data);
        $this->Recipe_item_model->delete_by_recipe_id($recipe_id);

        $item_ids = $this->input->post('item_id');
        $quantities = $this->input->post('quantity');
        $units = $this->input->post('unit');

        if (!empty($item_ids)) {
            for ($i = 0; $i < count($item_ids); $i++) {
                $recipe_item_data = array(
                    'recipe_id' => $recipe_id,
                    'item_id' => $item_ids[$i],
                    'quantity' => $quantities[$i],
                    'unit' => $units[$i],
                );
                $this->Recipe_item_model->insert($recipe_item_data);
            }
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo "Failed to update recipe. Please try again.";
        } else {
            $this->db->trans_commit();
            echo "success";
        }
    }

    public function delete($id)
    {
        $this->db->trans_begin();
        $this->Recipe_item_model->delete_by_recipe_id($id);
        $this->Recipe_model->delete($id);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo "failed";
        } else {
            $this->db->trans_commit();
            echo "success";
        }
    }

    public function ajax_list()
    {
        // Use Recipe_model datatables helpers
        $list = $this->Recipe_model->get_datatables();

        $data = array();
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        foreach ($list as $idx => $recipe) {

            $row = array();
            $row[] = '<input type="checkbox" name="checkbox[]" value="' . $recipe->id . '" class="checkbox column_checkbox" >';

            $row[] = isset($recipe->recipe_name) ? $recipe->recipe_name : '';

            // output product name (guard if missing)
            $output_product_name = '';
            if (isset($recipe->output_product_id) && $recipe->output_product_id) {
                $prod = $this->Items_model->get_by_id($recipe->output_product_id);
                $output_product_name = ($prod && isset($prod->item_name)) ? $prod->item_name : '';
            }
            $row[] = $output_product_name;

            $row[] = isset($recipe->yield_quantity) ? $recipe->yield_quantity : '';
            $row[] = isset($recipe->notes) ? $recipe->notes : '';
            $row[] = $this->_get_username(isset($recipe->created_by) ? $recipe->created_by : null);
            $row[] = isset($recipe->created_at) ? show_date($recipe->created_at) : '';

            // action buttons
            $buttons = '';
            if ($this->permissions('recipe_edit')) {
                $buttons .= '<a class="btn btn-primary btn-sm" href="' . base_url('recipe/edit/' . $recipe->id) . '" title="Edit"><i class="fa fa-edit"></i></a>';
            }
            if ($this->permissions('recipe_delete')) {
                $buttons .= ' <a class="btn btn-danger btn-sm" href="javascript:void(0)" title="Delete" onclick="delete_recipe(' . $recipe->id . ')"><i class="fa fa-trash"></i></a>';
            }
            $row[] = $buttons;

            $data[] = $row;
        }

        $output = array(
            "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
            "recordsTotal" => $this->Recipe_model->count_all(),
            "recordsFiltered" => $this->Recipe_model->count_filtered(),
            "data" => $data,
        );
        // output to json format
        echo json_encode($output);
    }

    /**
     * Safe wrapper to obtain username. Avoids fatal if login/users model is not available.
     */
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
    public function get_json_items_details()
    {
        // Check if it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }

        $term = $this->input->get('name');
        $this->load->model('Items_model');

        $items = $this->Items_model->search_items($term);
        $result = array();

        foreach ($items as $item) {
            // Check stock
            $stock = floatval($item->stock);
            $price = floatval($item->purchase_price);

            $result[] = array(
                'id' => $item->id,
                'value' => $item->item_name,
                'label' => $item->item_name . ' (' . $item->item_code . ')',
                'item_code' => $item->item_code,
                'stock' => $stock,
                'unit_name' => $item->unit_name,
                'purchase_price' => $price,
                'has_stock' => ($stock > 0)
            );
        }

        echo json_encode($result);
    }
}
