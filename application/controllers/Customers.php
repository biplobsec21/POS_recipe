<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Customers extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load_global();
		$this->load->model('customers_model', 'customers');
	}

	public function index()
	{
		$this->permission_check('customers_view');
		$data = $this->data;
		$data['page_title'] = $this->lang->line('customers_list');
		$this->load->view('customers-view', $data);
	}
	public function add()
	{
		$this->permission_check('customers_add');
		$data = $this->data;
		$data['page_title'] = $this->lang->line('customers');
		$this->load->view('customers', $data);
	}

	public function newcustomers()
	{
		$this->form_validation->set_rules('customer_name', 'Customer Name', 'trim|required');


		if ($this->form_validation->run() == TRUE) {
			$result = $this->customers->verify_and_save();
			echo $result;
		} else {
			echo "Please Fill Compulsory(* marked) Fields.";
		}
	}
	public function update($id)
	{
		$this->permission_check('customers_edit');
		$data = $this->data;
		$result = $this->customers->get_details($id, $data);
		$data = array_merge($data, $result);
		$data['page_title'] = $this->lang->line('customers');
		$this->load->view('customers', $data);
	}
	public function allpayments($id)
	{
		$this->permission_check('customers_edit');
		$data = $this->data;
		$result = $this->customers->get_details($id, $data);
		$data = array_merge($data, $result);
		$data['page_title'] = $this->lang->line('customers');
		$this->load->view('customer-payments', $data);
	}
	public function update_customers()
	{
		$this->form_validation->set_rules('customer_name', 'Customer Name', 'trim|required');

		if ($this->form_validation->run() == TRUE) {
			$result = $this->customers->update_customers();
			echo $result;
		} else {
			echo "Please Fill Compulsory(* marked) Fields.";
		}
	}

	public function show_total_customer_paid_amount($customer_id)
	{
		return $this->db->select("coalesce(sum(paid_amount),0) as tot")->where("customer_id", $customer_id)->get("db_sales")->row()->tot;
	}
	public function ajax_list()
	{
		$list = $this->customers->get_datatables();

		$data = array();
		$no = $_POST['start'];
		foreach ($list as $customers) {
			$no++;
			$row = array();
			$disable = ($customers->id == 1) ? 'disabled' : '';
			if ($customers->id == 1) {
				$row[] = '<span class="text-blue">NA</span>';
			} else {
				$row[] = '<input type="checkbox" name="checkbox[]" ' . $disable . ' value=' . $customers->id . ' class="checkbox column_checkbox" >';
			}

			$row[] = $customers->customer_code;
			$row[] = $customers->customer_name;
			$row[] = $customers->mobile;
			$row[] = $customers->address;
			$row[] = app_number_format($this->show_total_customer_paid_amount($customers->id));
			$row[] = (!empty($customers->sales_due) && $customers->sales_due != 0) ? app_number_format($customers->sales_due) : (0);
			$row[] = ($customers->sales_return_due == null) ? $customers->opening_balance : app_number_format($customers->sales_return_due);
			$row[] = $this->customers->get_total_due_amount($customers->id);

			if ($customers->status == 1) {
				$str = "<span onclick='update_status(" . $customers->id . ",0)' id='span_" . $customers->id . "'  class='label label-success' style='cursor:pointer'>Active </span>";
			} else {
				$str = "<span onclick='update_status(" . $customers->id . ",1)' id='span_" . $customers->id . "'  class='label label-danger' style='cursor:pointer'> Inactive </span>";
			}
			$row[] = $str;
			$str2 = '<div class="btn-group" title="View Account">
										<a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" href="#">
											Action <span class="caret"></span>
										</a>
										<ul role="menu" class="dropdown-menu dropdown-light pull-right">';

			if ($this->permissions('customers_edit') && $customers->id != 1)
				$str2 .= '<li>
												<a title="Edit Record ?" href="customers/update/' . $customers->id . '">
													<i class="fa fa-fw fa-edit text-blue"></i>Edit
												</a>
											</li>';
			// In customers-view.php, add to the action dropdown menu:
			$str2 .= '<li>
				<a title="Customer Ledger" href="' . base_url('customers/customer_ledger/' . $customers->id) . '">
					<i class="fa fa-fw fa-book text-purple"></i>Customer Ledger
				</a>
			</li>';
			$str2 .= '<li>
												<a title="Edit Record ?" href="customers/allpayments/' . $customers->id . '">
												<i class="fa fa-fw fa-money text-blue"></i> Customer Payments
												</a>
											</li>';
			if ($this->permissions('sales_payment_add'))
				$str2 .= '<li>
												<a title="Pay Opening Balance & Sales Due Payments" class="pointer" onclick="pay_now(' . $customers->id . ')" >
													<i class="fa fa-fw fa-money text-blue"></i>Receive Due Payments
												</a>
											</li>';
			if ($this->permissions('sales_return_payment_add'))
				$str2 .= '<li>
												<a title="Pay Return Due" class="pointer" onclick="pay_return_due(' . $customers->id . ')" >
													<i class="fa fa-fw fa-money text-blue"></i>Pay Return Due
												</a>
											</li>';
			if ($this->permissions('customers_delete') && $customers->id != 1)
				$str2 .= '<li>
												<a style="cursor:pointer" title="Delete Record ?" onclick="delete_customers(' . $customers->id . ')">
													<i class="fa fa-fw fa-trash text-red"></i>Delete
												</a>
											</li>
											
										</ul>

									</div>';
			$row[] =  $str2;

			$data[] = $row;
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->customers->count_all(),
			"recordsFiltered" => $this->customers->count_filtered(),
			"data" => $data,
		);
		//output to json format
		echo json_encode($output);
	}
	public function update_status()
	{
		$this->permission_check_with_msg('customers_edit');
		$id = $this->input->post('id');
		$status = $this->input->post('status');

		$result = $this->customers->update_status($id, $status);
		return $result;
	}

	public function delete_customers()
	{
		$this->permission_check_with_msg('customers_delete');
		$id = $this->input->post('q_id');
		return $this->customers->delete_customers_from_table($id);
	}
	public function multi_delete()
	{
		$this->permission_check_with_msg('customers_delete');
		$ids = implode(",", $_POST['checkbox']);
		return $this->customers->delete_customers_from_table($ids);
	}
	public function show_pay_now_modal()
	{
		$this->permission_check_with_msg('sales_payment_add');
		$customer_id = $this->input->post('customer_id');
		echo $this->customers->show_pay_now_modal($customer_id);
	}
	public function save_payment()
	{
		$this->permission_check_with_msg('sales_payment_add');
		echo $this->customers->save_payment();
	}
	public function show_pay_return_due_modal()
	{
		$this->permission_check_with_msg('sales_return_payment_add');
		$customer_id = $this->input->post('customer_id');
		echo $this->customers->show_pay_return_due_modal($customer_id);
	}
	public function save_return_due_payment()
	{
		$this->permission_check_with_msg('sales_payment_add');
		echo $this->customers->save_return_due_payment();
	}
	public function delete_opening_balance_entry()
	{
		$this->permission_check_with_msg('sales_payment_delete');
		$entry_id = $this->input->post('entry_id');
		echo $this->customers->delete_opening_balance_entry($entry_id);
	}
	public function getCustomers($id = '')
	{
		echo $this->customers->getCustomersJson($id);
	}
	// Add these methods to your existing Customers controller

	// ledger implementation
	// ledger implementation
	public function customer_ledger($customer_id = null)
	{
		$this->permission_check('customers_view');
		$data = $this->data;

		$this->load->model('customer_ledger_model');

		// Get all active customers for the global dropdown - ALWAYS get this
		$data['all_customers'] = $this->db->where('status', 1)
			->order_by('customer_name', 'ASC')
			->get('db_customers')
			->result();

		// Check if customer_id is provided via POST from global dropdown
		$post_customer_id = $this->input->post('global_customer_id');
		if ($post_customer_id) {
			$customer_id = $post_customer_id;
		}

		// Set default date range to 12 months
		$start_date = $this->input->get('start_date') ? $this->input->get('start_date') : ($this->input->post('start_date') ? $this->input->post('start_date') :
			date('01-m-Y', strtotime('-11 months')));

		$end_date = $this->input->get('end_date') ? $this->input->get('end_date') : ($this->input->post('end_date') ? $this->input->post('end_date') :
			date('t-m-Y'));

		$data['start_date'] = $start_date;
		$data['end_date'] = $end_date;

		// Get customer details if customer_id is provided
		if ($customer_id) {
			$customer_info = $this->customer_ledger_model->get_customer_details($customer_id);
			if (!$customer_info) {
				show_404();
			}
			$data['customer_info'] = $customer_info;
			$data['customer_id'] = $customer_id;

			// Get ledger data only when customer is selected
			$data['ledger_data'] = $this->customer_ledger_model->get_customer_ledger($customer_id, $start_date, $end_date);
			$data['account_summary'] = $this->customer_ledger_model->get_account_summary($customer_id, $start_date, $end_date);
		}

		$data['page_title'] = "Customer Ledger";
		$this->load->view('customer_ledger', $data);
	}

	public function ajax_customer_ledger()
	{
		$this->permission_check('customers_view');

		$customer_id = $this->input->get('customer_id');
		$start_date = $this->input->get('start_date');
		$end_date = $this->input->get('end_date');

		$this->load->model('customer_ledger_model');

		$ledger_data = $this->customer_ledger_model->get_customer_ledger($customer_id, $start_date, $end_date);
		$account_summary = $this->customer_ledger_model->get_account_summary($customer_id, $start_date, $end_date);

		echo json_encode([
			'ledger_data' => $ledger_data,
			'account_summary' => $account_summary
		]);
	}

	public function print_ledger($customer_id)
	{
		$this->permission_check('customers_view');
		$data = $this->data;

		$this->load->model('customer_ledger_model');

		$start_date = $this->input->get('start_date') ? $this->input->get('start_date') : date('01-m-Y');
		$end_date = $this->input->get('end_date') ? $this->input->get('end_date') : date('t-m-Y');

		$customer_info = $this->customer_ledger_model->get_customer_details($customer_id);
		$ledger_data = $this->customer_ledger_model->get_customer_ledger($customer_id, $start_date, $end_date);
		$account_summary = $this->customer_ledger_model->get_account_summary($customer_id, $start_date, $end_date);

		$data['customer_info'] = $customer_info;
		$data['ledger_data'] = $ledger_data;
		$data['account_summary'] = $account_summary;
		$data['start_date'] = $start_date;
		$data['end_date'] = $end_date;

		$this->load->view('print_customer_ledger', $data);
	}
}
