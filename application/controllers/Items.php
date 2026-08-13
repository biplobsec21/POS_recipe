<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Items extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load_global();
		$this->load->model('items_model', 'items');
	}

	public function index()
	{
		$this->permission_check('items_view');
		$data = $this->data;
		$data['page_title'] = $this->lang->line('items_list');
		$this->load->view('items-list', $data);
	}
	public function add()
	{
		$this->permission_check('items_add');
		$data = $this->data;
		$data['page_title'] = $this->lang->line('items');
		$this->load->view('items', $data);
	}

	public function newitems()
	{
		$this->form_validation->set_rules('item_name', 'Item Name', 'trim|required');
		$this->form_validation->set_rules('category_id', 'Category Name', 'trim|required');
		$this->form_validation->set_rules('unit_id', 'Unit', 'trim|required');
		$this->form_validation->set_rules('price', 'Item Price', 'trim|required');
		$this->form_validation->set_rules('tax_id', 'Tax', 'trim|required');
		$this->form_validation->set_rules('purchase_price', 'Purchase Price', 'trim|required');
		//$this->form_validation->set_rules('profit_margin', 'Profit Margin', 'trim|required');
		$this->form_validation->set_rules('sales_price', 'Sales Price', 'trim|required');


		if ($this->form_validation->run() == TRUE) {
			$result = $this->items->verify_and_save();
			echo $result;
		} else {
			echo "Please Fill Compulsory(* marked) Fields.";
		}
	}
	public function update($id)
	{
		$this->permission_check('items_edit');
		$data = $this->data;
		$this->load->model('items_model');
		$result = $this->items_model->get_details($id, $data);
		$data = array_merge($data, $result);
		$data['page_title'] = $this->lang->line('items');
		$this->load->view('items', $data);
	}
	public function update_items()
	{
		$this->form_validation->set_rules('item_name', 'Item Name', 'trim|required');
		$this->form_validation->set_rules('category_id', 'Category Name', 'trim|required');
		$this->form_validation->set_rules('unit_id', 'Unit', 'trim|required');
		$this->form_validation->set_rules('price', 'Item Price', 'trim|required');
		$this->form_validation->set_rules('tax_id', 'Tax', 'trim|required');
		$this->form_validation->set_rules('purchase_price', 'Purchase Price', 'trim|required');
		//$this->form_validation->set_rules('profit_margin', 'Profit Margin', 'trim|required');
		$this->form_validation->set_rules('sales_price', 'Sales Price', 'trim|required');

		if ($this->form_validation->run() == TRUE) {
			$result = $this->items->update_items();
			echo $result;
		} else {
			echo "Please Fill Compulsory(* marked) Fields.";
		}
	}

	public function get_brand_name($brand_id = '')
	{
		if ($brand_id == NULL || $brand_id == '' || $brand_id == 0) {
			return;
		}
		return $this->db->query('select brand_name from db_brands where id="' . $brand_id . '"')->row()->brand_name;
	}
	public function ajax_list()
	{
		$list = $this->items->get_datatables();

		$data = array();
		$no = $_POST['start'];
		$tax_disabled = (is_tax_disabled()) ? true : false;
		foreach ($list as $items) {

			$no++;
			$row = array();
			$row[] = '<input type="checkbox" name="checkbox[]" value=' . $items->id . ' class="checkbox column_checkbox" >';


			$row[] = (!empty($items->item_image) && file_exists($items->item_image)) ? "
						<a title='Click for Bigger!' href='" . base_url($items->item_image) . "' data-toggle='lightbox'>
						<image style='border:1px #72afd2 solid;' src='" . base_url(return_item_image_thumb($items->item_image)) . "' width='75%' height='50%'> </a>" : "
						<image style='border:1px #72afd2 solid;' src='" . base_url() . "theme/images/no_image.png' title='No Image!' width='75%' height='50%' >";
			$row[] = $items->item_code;
			$row[] = "<label class='text-blue'>" . $items->item_name . "</label><br><b>HSN</b>:" . $items->hsn . "<br><b>SKU</b>:" . $items->sku;
			$row[] = $items->brand_name; //$this->get_brand_name($items->brand_id);
			$row[] = $items->category_name;
			$row[] = $items->unit_name;
			$row[] = $items->stock;
			$row[] = $items->alert_qty;
			// $row[] = app_number_format($items->purchase_price);
			// if($this->permissions('don_not_show_purchase_unit_price_view') && $this->session->userdata('inv_userid') != 1){

			// }else{
			// 	$row[] = app_number_format($items->purchase_price);
			// }
			$row[] = app_number_format($items->purchase_price);
			$row[] = app_number_format($items->final_price);
			$row[] = ($tax_disabled) ? '<p class="text-yellow text-bold">Disabled</p>' : $items->tax_name . "<br>(" . $items->tax_type . ")";

			if ($items->status == 1) {
				$str = "<span onclick='update_status(" . $items->id . ",0)' id='span_" . $items->id . "'  class='label label-success' style='cursor:pointer'>Active </span>";
			} else {
				$str = "<span onclick='update_status(" . $items->id . ",1)' id='span_" . $items->id . "'  class='label label-danger' style='cursor:pointer'> Inactive </span>";
			}
			$row[] = $str;

			$str2 = '<div class="btn-group" title="View Account">
										<a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" href="#">
											Action <span class="caret"></span>
										</a>
										<ul role="menu" class="dropdown-menu dropdown-light pull-right">';

			if ($this->permissions('items_edit'))
				$str2 .= '<li>
												<a title="Edit Record ?" href="' . base_url('items/update/' . $items->id) . '">
													<i class="fa fa-fw fa-edit text-blue"></i>Edit
												</a>
											</li>';
			if ($this->permissions('items_view'))
				$str2 .= '<li>
					<a title="Stock History" href="' . base_url('items/stock_history/' . $items->id) . '">
						<i class="fa fa-fw fa-history text-green"></i>Stock History
					</a>
				</li>';
			if ($this->permissions('items_delete'))
				$str2 .= '<li>
												<a style="cursor:pointer" title="Delete Record ?" onclick="delete_items(' . $items->id . ')">
													<i class="fa fa-fw fa-trash text-red"></i>Delete
												</a>
											</li>
											
										</ul>
									</div>';

			$row[] = $str2;

			$data[] = $row;
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->items->count_all(),
			"recordsFiltered" => $this->items->count_filtered(),
			"data" => $data,
		);
		//output to json format
		echo json_encode($output);
	}
	public function update_status()
	{
		$this->permission_check_with_msg('items_edit');
		$id = $this->input->post('id');
		$status = $this->input->post('status');

		$this->load->model('items_model');
		$result = $this->items_model->update_status($id, $status);
		return $result;
	}

	public function delete_items()
	{
		$this->permission_check_with_msg('items_delete');
		$id = $this->input->post('q_id');
		return $this->items->delete_items_from_table($id);
	}
	public function multi_delete()
	{
		$this->permission_check_with_msg('items_delete');
		$ids = implode(",", $_POST['checkbox']);
		return $this->items->delete_items_from_table($ids);
	}

	//Used in Purchase and sales Forms
	public function get_json_items_details()
	{
		$data = array();
		$display_json = array();

		if (isset($_GET['name']) && !empty($_GET['name'])) {
			// Sanitize and escape the input
			$name = $this->input->get('name', TRUE); // TRUE enables XSS filtering
			$name = trim($name);

			// Escape for LIKE search
			$name = $this->db->escape_like_str($name);
			$name = $this->db->escape_str($name);

			// Build safe query with parameter binding
			$search_term = '%' . $name . '%';
			$escaped_search = $this->db->escape($search_term);

			$sql = $this->db->query("SELECT id, item_name, item_code, stock 
                                FROM db_items 
                                WHERE status = 1 
                                AND (LOWER(item_name) LIKE $escaped_search 
                                     OR LOWER(item_code) LIKE $escaped_search 
                                     OR LOWER(custom_barcode) LIKE $escaped_search) 
                                LIMIT 10");

			// Add error handling
			if ($sql === FALSE) {
				// Log the error
				$error = $this->db->error();
				log_message('error', 'Database error in get_json_items_details: ' . $error['message']);
				echo json_encode($display_json);
				exit;
			}

			foreach ($sql->result() as $res) {
				$json_arr["id"] = $res->id;
				$json_arr["value"] = $res->item_name;
				$json_arr["label"] = $res->item_name;
				$json_arr["item_code"] = $res->item_code;
				$json_arr["stock"] = $res->stock;
				array_push($display_json, $json_arr);
			}
		}

		echo json_encode($display_json);
		exit;
	}

	public function labels($purchase_id = '')
	{
		$this->permission_check('print_labels');
		$data = $this->data;
		$data['page_title'] = $this->lang->line('print_labels');
		$data['purchase_id'] = $purchase_id;
		$this->load->view('labels', $data);
	}

	/*Labels Print request*/
	public function return_row_with_data($rowcount, $item_id)
	{
		echo $this->items->get_items_info($rowcount, $item_id);
	}

	public function preview_labels()
	{
		echo $this->items->preview_labels();
	}

	//GET Labels from Purchase Invoice
	public function show_labels($purchase_id = '')
	{
		$i = 1;
		$result = '';
		$q2 = $this->db->query("select item_id,purchase_qty from db_purchaseitems where purchase_id='$purchase_id'");
		if ($q2->num_rows() > 0) {

			foreach ($q2->result() as $res2) {
				$result .= $this->items->get_purchase_items_info($i++, $res2->item_id, $res2->purchase_qty);
			}
		}
		echo $result;
	}
	public function delete_stock_entry()
	{
		$this->permission_check_with_msg('items_delete');
		$entry_id = $this->input->post('entry_id');
		echo $this->items->delete_stock_entry($entry_id);
	}
	public function getItems($id = '')
	{
		echo $this->items->getItemsJson($id);
	}
	// Add this method to your Items controller
	public function stock_history($item_id)
	{
		$this->permission_check('items_view');
		$data = $this->data;

		// Load the stock history model
		$this->load->model('stock_history_model');

		// Get item details
		$item_info = $this->stock_history_model->get_item_details($item_id);
		if (!$item_info) {
			show_404();
		}

		$stock_summary = $this->stock_history_model->get_stock_summary($item_id);
		$transactions = $this->stock_history_model->get_transaction_history($item_id, 0, 1000);

		$item_info->stock = $stock_summary['current_stock'];
		$data['item_info'] = $item_info;
		$data['stock_summary'] = $stock_summary;
		$data['transactions'] = $transactions;

		// Check if we got any data
		if (empty($data['transactions']) && !$this->input->is_ajax_request()) {
			$this->session->set_flashdata('warning', 'No transaction history found for this item.');
		}



		$data['page_title'] = "Stock History" . ' - ' . $item_info->item_name;
		$data['q_id'] = $item_id;

		$this->load->view('stock_history', $data);
	}

	public function sync_current_stock($item_id)
	{
		$this->permission_check_with_msg('items_edit');
		$this->load->model('stock_history_model');

		$summary = $this->stock_history_model->get_stock_summary($item_id);
		$current_stock = isset($summary['current_stock']) ? (float) $summary['current_stock'] : 0;

		$this->db->where('id', $item_id);
		$this->db->update('db_items', array('stock' => $current_stock));

		if ($this->db->error()['code'] !== 0) {
			echo json_encode(array('success' => false, 'message' => 'Database update failed.'));
			return;
		}

		echo json_encode(array('success' => true, 'stock' => $current_stock));
	}

	public function sync_all_items_stock()
	{
		$this->permission_check_with_msg('items_edit');
		$this->load->model('stock_history_model');

		try {
			// Get all active items
			$query = $this->db->select('id, item_name, item_code, stock')->from('db_items')->where('status', 1)->get();
			$items = $query->result();

			if (empty($items)) {
				echo json_encode(array('success' => false, 'message' => 'No items found to synchronize.'));
				return;
			}

			$sync_count = 0;
			$error_count = 0;
			$synced_items = array();
			$error_items = array();

			foreach ($items as $item) {
				$summary = $this->stock_history_model->get_stock_summary($item->id);
				$ledger_stock = isset($summary['current_stock']) ? (float) $summary['current_stock'] : 0;
				$old_stock = (float) $item->stock;

				// Check if there's a discrepancy
				if ($old_stock != $ledger_stock) {
					$this->db->where('id', $item->id);
					$update_result = $this->db->update('db_items', array('stock' => $ledger_stock));

					if ($update_result) {
						$sync_count++;
						$synced_items[] = array(
							'item_id' => $item->id,
							'item_code' => $item->item_code,
							'item_name' => $item->item_name,
							'old_stock' => $old_stock,
							'new_stock' => $ledger_stock,
							'difference' => $ledger_stock - $old_stock
						);

						// Log to database
						$this->_log_stock_sync($item->id, $item->item_code, $item->item_name, $old_stock, $ledger_stock);
					} else {
						$error_count++;
						$error_items[] = array(
							'item_id' => $item->id,
							'item_code' => $item->item_code,
							'item_name' => $item->item_name,
							'error' => 'Failed to update'
						);
					}
				}
			}

			$message = "Successfully synchronized " . $sync_count . " item(s) with discrepancies.";
			if ($error_count > 0) {
				$message .= " (" . $error_count . " failed)";
			}

			echo json_encode(array(
				'success' => true,
				'message' => $message,
				'synced' => $sync_count,
				'failed' => $error_count,
				'synced_items' => $synced_items,
				'error_items' => $error_items
			));
		} catch (Exception $e) {
			echo json_encode(array('success' => false, 'message' => 'Error: ' . $e->getMessage()));
		}
	}

	private function _log_stock_sync($item_id, $item_code, $item_name, $old_stock, $new_stock)
	{
		// Check if stock_sync_log table exists, create if not
		if (!$this->db->table_exists('stock_sync_log')) {
			$this->db->query("
				CREATE TABLE IF NOT EXISTS stock_sync_log (
					id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					item_id INT(11) NOT NULL,
					item_code VARCHAR(255),
					item_name VARCHAR(255),
					old_stock DECIMAL(20, 4),
					new_stock DECIMAL(20, 4),
					difference DECIMAL(20, 4),
					synced_by INT(11),
					synced_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					KEY item_id (item_id)
				)
			");
		}

		// Insert log entry
		$log_data = array(
			'item_id' => $item_id,
			'item_code' => $item_code,
			'item_name' => $item_name,
			'old_stock' => $old_stock,
			'new_stock' => $new_stock,
			'difference' => $new_stock - $old_stock,
			'synced_by' => $this->session->userdata('inv_userid'),
			'synced_at' => date('Y-m-d H:i:s')
		);

		$this->db->insert('stock_sync_log', $log_data);
	}
	// AJAX method for paginated transactions
	// AJAX method for paginated transactions
	public function ajax_stock_history()
	{
		$this->permission_check('items_view');

		$item_id = $this->input->get('item_id');
		$start = $this->input->get('start') ?? 0;
		$length = $this->input->get('length') ?? 25;

		$this->load->model('stock_history_model');

		$transactions = $this->stock_history_model->get_transaction_history($item_id, $start, $length);
		$total_count = $this->stock_history_model->count_total_transactions($item_id);

		// Format dates for JSON response
		foreach ($transactions as $transaction) {
			$transaction->transaction_date = date('d-m-Y h:i A', strtotime($transaction->transaction_date));
		}

		echo json_encode([
			'data' => $transactions,
			'recordsTotal' => $total_count,
			'recordsFiltered' => $total_count
		]);
	}
}
