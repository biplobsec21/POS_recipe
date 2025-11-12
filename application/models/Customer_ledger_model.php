<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Customer_ledger_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // Get customer details
    public function get_customer_details($customer_id)
    {
        $this->db->select('*');
        $this->db->from('db_customers');
        $this->db->where('id', $customer_id);
        $this->db->where('status', 1);

        $query = $this->db->get();
        return $query ? $query->row() : null;
    }

    // Get comprehensive customer ledger
    public function get_customer_ledger($customer_id, $start_date, $end_date)
    {
        $ledger_data = array();

        // Convert dates to database format
        $db_start_date = date('Y-m-d', strtotime($start_date));
        $db_end_date = date('Y-m-d', strtotime($end_date));

        // 1. Opening Balance
        $opening_balance = $this->get_opening_balance($customer_id, $db_start_date);
        if ($opening_balance != 0) {
            $ledger_data[] = (object)[
                'date' => $db_start_date . ' 00:00:00',
                'reference_no' => '',
                'type' => 'Opening Balance',
                'location' => '',
                'payment_status' => '',
                'debit' => $opening_balance > 0 ? abs($opening_balance) : 0,
                'credit' => $opening_balance < 0 ? abs($opening_balance) : 0,
                'balance' => $opening_balance,
                'payment_method' => '',
                'others' => '',
                'items' => array(),
                'is_opening' => true
            ];
        }

        $running_balance = $opening_balance;

        // 2. Sales Invoices
        $sales = $this->get_sales_transactions($customer_id, $db_start_date, $db_end_date);
        foreach ($sales as $sale) {
            $running_balance += $sale->grand_total;
            $sale->balance = $running_balance;
            $sale->type = 'Sell';
            $sale->is_opening = false;
            $ledger_data[] = $sale;
        }

        // 3. Sales Returns
        $sales_returns = $this->get_sales_return_transactions($customer_id, $db_start_date, $db_end_date);
        foreach ($sales_returns as $return) {
            $running_balance -= $return->grand_total;
            $return->balance = $running_balance;
            $return->type = 'Sales Return';
            $return->is_opening = false;
            $ledger_data[] = $return;
        }

        // 4. Customer Payments
        $payments = $this->get_customer_payments($customer_id, $db_start_date, $db_end_date);
        foreach ($payments as $payment) {
            $running_balance -= $payment->payment;
            $payment->balance = $running_balance;
            $payment->type = 'Payment';
            $payment->is_opening = false;
            $ledger_data[] = $payment;
        }

        // 5. Sales Return Payments (when we pay customer for returns)
        $return_payments = $this->get_sales_return_payments($customer_id, $db_start_date, $db_end_date);
        foreach ($return_payments as $return_payment) {
            $running_balance += $return_payment->payment;
            $return_payment->balance = $running_balance;
            $return_payment->type = 'Return Payment';
            $return_payment->is_opening = false;
            $ledger_data[] = $return_payment;
        }

        // Sort by date
        usort($ledger_data, function ($a, $b) {
            return strtotime($a->date) - strtotime($b->date);
        });

        return $ledger_data;
    }

    // Get opening balance before start date
    private function get_opening_balance($customer_id, $start_date)
    {
        $balance = 0;

        // Opening balance from customer record
        $this->db->select('opening_balance');
        $this->db->from('db_customers');
        $this->db->where('id', $customer_id);
        $customer = $this->db->get()->row();
        $balance += $customer ? $customer->opening_balance : 0;

        // Sales before start date
        $this->db->select('COALESCE(SUM(grand_total), 0) as total_sales');
        $this->db->from('db_sales');
        $this->db->where('customer_id', $customer_id);
        $this->db->where('sales_date <', $start_date);
        $this->db->where('status', 1);
        $sales = $this->db->get()->row();
        $balance += $sales->total_sales;

        // Sales returns before start date
        $this->db->select('COALESCE(SUM(grand_total), 0) as total_returns');
        $this->db->from('db_salesreturn');
        $this->db->where('customer_id', $customer_id);
        $this->db->where('return_date <', $start_date);
        $this->db->where('status', 1);
        $returns = $this->db->get()->row();
        $balance -= $returns->total_returns;

        // Payments before start date
        $this->db->select('COALESCE(SUM(payment), 0) as total_payments');
        $this->db->from('db_salespayments');
        $this->db->where('sales_id IN (SELECT id FROM db_sales WHERE customer_id = ' . $customer_id . ')');
        $this->db->where('payment_date <', $start_date);
        $this->db->where('status', 1);
        $payments = $this->db->get()->row();
        $balance -= $payments->total_payments;

        // Return payments before start date
        $this->db->select('COALESCE(SUM(payment), 0) as total_return_payments');
        $this->db->from('db_salespaymentsreturn');
        $this->db->where('return_id IN (SELECT id FROM db_salesreturn WHERE customer_id = ' . $customer_id . ')');
        $this->db->where('payment_date <', $start_date);
        $this->db->where('status', 1);
        $return_payments = $this->db->get()->row();
        $balance += $return_payments->total_return_payments;

        return $balance;
    }

    // Get sales transactions
    private function get_sales_transactions($customer_id, $start_date, $end_date)
    {
        $this->db->select('s.id, s.sales_code as reference_no, s.sales_date as date, 
                          s.grand_total, s.payment_status, w.warehouse_name as location,
                          si.item_id, i.item_name, si.sales_qty, si.price_per_unit, 
                          si.discount_amt, si.tax_amt, si.total_cost');
        $this->db->from('db_sales s');
        $this->db->join('db_warehouse w', 'w.id = s.warehouse_id', 'left');
        $this->db->join('db_salesitems si', 'si.sales_id = s.id', 'left');
        $this->db->join('db_items i', 'i.id = si.item_id', 'left');
        $this->db->where('s.customer_id', $customer_id);
        $this->db->where('s.sales_date >=', $start_date);
        $this->db->where('s.sales_date <=', $end_date);
        $this->db->where('s.status', 1);
        $this->db->order_by('s.sales_date', 'ASC');

        $query = $this->db->get();
        $sales = $query->result();

        // Group by sales invoice
        $grouped_sales = array();
        foreach ($sales as $sale) {
            if (!isset($grouped_sales[$sale->id])) {
                $grouped_sales[$sale->id] = (object)[
                    'date' => $sale->date,
                    'reference_no' => $sale->reference_no,
                    'grand_total' => $sale->grand_total,
                    'payment_status' => $sale->payment_status,
                    'location' => $sale->location,
                    'debit' => $sale->grand_total,
                    'credit' => 0,
                    'payment_method' => '',
                    'others' => '',
                    'items' => array()
                ];
            }

            if ($sale->item_id) {
                $grouped_sales[$sale->id]->items[] = (object)[
                    'item_name' => $sale->item_name,
                    'sales_qty' => $sale->sales_qty,
                    'price_per_unit' => $sale->price_per_unit,
                    'discount_amt' => $sale->discount_amt,
                    'tax_amt' => $sale->tax_amt,
                    'total_cost' => $sale->total_cost
                ];
            }
        }

        return array_values($grouped_sales);
    }

    // Get sales return transactions
    private function get_sales_return_transactions($customer_id, $start_date, $end_date)
    {
        $this->db->select('sr.id, sr.return_code as reference_no, sr.return_date as date, 
                          sr.grand_total, sr.payment_status, w.warehouse_name as location,
                          sri.item_id, i.item_name, sri.return_qty, sri.price_per_unit, 
                          sri.discount_amt, sri.tax_amt, sri.total_cost');
        $this->db->from('db_salesreturn sr');
        $this->db->join('db_warehouse w', 'w.id = sr.warehouse_id', 'left');
        $this->db->join('db_salesitemsreturn sri', 'sri.return_id = sr.id', 'left');
        $this->db->join('db_items i', 'i.id = sri.item_id', 'left');
        $this->db->where('sr.customer_id', $customer_id);
        $this->db->where('sr.return_date >=', $start_date);
        $this->db->where('sr.return_date <=', $end_date);
        $this->db->where('sr.status', 1);
        $this->db->order_by('sr.return_date', 'ASC');

        $query = $this->db->get();
        $returns = $query->result();

        // Group by return invoice
        $grouped_returns = array();
        foreach ($returns as $return) {
            if (!isset($grouped_returns[$return->id])) {
                $grouped_returns[$return->id] = (object)[
                    'date' => $return->date,
                    'reference_no' => $return->reference_no,
                    'grand_total' => $return->grand_total,
                    'payment_status' => $return->payment_status,
                    'location' => $return->location,
                    'debit' => 0,
                    'credit' => $return->grand_total,
                    'payment_method' => '',
                    'others' => '',
                    'items' => array()
                ];
            }

            if ($return->item_id) {
                $grouped_returns[$return->id]->items[] = (object)[
                    'item_name' => $return->item_name,
                    'return_qty' => $return->return_qty,
                    'price_per_unit' => $return->price_per_unit,
                    'discount_amt' => $return->discount_amt,
                    'tax_amt' => $return->tax_amt,
                    'total_cost' => $return->total_cost
                ];
            }
        }

        return array_values($grouped_returns);
    }

    // Get customer payments
    private function get_customer_payments($customer_id, $start_date, $end_date)
    {
        $this->db->select('sp.id, sp.payment_date as date, sp.payment_type as payment_method, 
                      sp.payment_note as others, sp.payment,
                      CONCAT("SP", YEAR(sp.payment_date), "/", sp.id) as reference_no,
                      s.sales_code as sales_reference'); // Add sales reference for context
        $this->db->from('db_salespayments sp');
        $this->db->join('db_sales s', 's.id = sp.sales_id');
        $this->db->where('s.customer_id', $customer_id);
        $this->db->where('sp.payment_date >=', $start_date);
        $this->db->where('sp.payment_date <=', $end_date);
        $this->db->where('sp.status', 1);
        $this->db->order_by('sp.payment_date', 'ASC');

        $query = $this->db->get();
        $payments = $query->result();

        foreach ($payments as $payment) {
            $payment->debit = 0;
            $payment->credit = $payment->payment;
            $payment->location = '';
            $payment->payment_status = 'Paid'; // Set status as Paid for payments
            $payment->items = array();
            // Add sales reference to notes for context
            if (empty($payment->others)) {
                $payment->others = "Payment for " . $payment->sales_reference;
            }
        }

        return $payments;
    }

    // Get sales return payments
    private function get_sales_return_payments($customer_id, $start_date, $end_date)
    {
        $this->db->select('spr.id, spr.payment_date as date, spr.payment_type as payment_method, 
                      spr.payment_note as others, spr.payment,
                      CONCAT("RSP", YEAR(spr.payment_date), "/", spr.id) as reference_no,
                      sr.return_code as return_reference');
        $this->db->from('db_salespaymentsreturn spr');
        $this->db->join('db_salesreturn sr', 'sr.id = spr.return_id');
        $this->db->where('sr.customer_id', $customer_id);
        $this->db->where('spr.payment_date >=', $start_date);
        $this->db->where('spr.payment_date <=', $end_date);
        $this->db->where('spr.status', 1);
        $this->db->order_by('spr.payment_date', 'ASC');

        $query = $this->db->get();
        $return_payments = $query->result();

        foreach ($return_payments as $payment) {
            $payment->debit = $payment->payment;
            $payment->credit = 0;
            $payment->location = '';
            $payment->payment_status = 'Paid';
            $payment->items = array();
            // Add return reference to notes for context
            if (empty($payment->others)) {
                $payment->others = "Return payment for " . $payment->return_reference;
            }
        }

        return $return_payments;
    }

    // Get account summary
    public function get_account_summary($customer_id, $start_date, $end_date)
    {
        $db_start_date = date('Y-m-d', strtotime($start_date));
        $db_end_date = date('Y-m-d', strtotime($end_date));

        $summary = array(
            'opening_balance' => 0,
            'total_invoice' => 0,
            'total_paid' => 0,
            'advance_balance' => 0,
            'balance_due' => 0
        );

        // Opening balance
        $summary['opening_balance'] = $this->get_opening_balance($customer_id, $db_start_date);

        // Total invoice (sales within date range)
        $this->db->select('COALESCE(SUM(grand_total), 0) as total_invoice');
        $this->db->from('db_sales');
        $this->db->where('customer_id', $customer_id);
        $this->db->where('sales_date >=', $db_start_date);
        $this->db->where('sales_date <=', $db_end_date);
        $this->db->where('status', 1);
        $invoice_result = $this->db->get()->row();
        $summary['total_invoice'] = $invoice_result->total_invoice;

        // Total paid (payments within date range)
        $this->db->select('COALESCE(SUM(sp.payment), 0) as total_paid');
        $this->db->from('db_salespayments sp');
        $this->db->join('db_sales s', 's.id = sp.sales_id');
        $this->db->where('s.customer_id', $customer_id);
        $this->db->where('sp.payment_date >=', $db_start_date);
        $this->db->where('sp.payment_date <=', $db_end_date);
        $this->db->where('sp.status', 1);
        $paid_result = $this->db->get()->row();
        $summary['total_paid'] = $paid_result->total_paid;

        // Balance due
        $summary['balance_due'] = $summary['opening_balance'] + $summary['total_invoice'] - $summary['total_paid'];

        return $summary;
    }
}
