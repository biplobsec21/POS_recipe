<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Stock_history_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // Get comprehensive stock summary
    public function get_stock_summary($item_id)
    {
        $summary = array(
            'total_purchase' => 0,
            'opening_stock' => 0,
            'total_sell_return' => 0,
            'stock_transfers_in' => 0,
            'total_sold' => 0,
            'total_stock_adjustment' => 0,
            'total_purchase_return' => 0,
            'stock_transfers_out' => 0,
            'current_stock' => 0,
            'production_output' => 0,
            'production_consumption' => 0
        );

        // Get current stock
        $item = $this->db->select('stock as current_stock')->from('db_items')->where('id', $item_id)->get()->row();
        if ($item) {
            $summary['current_stock'] = $item->current_stock;
        }

        // Total Purchase
        $query = $this->db->query("
            SELECT COALESCE(SUM(pi.purchase_qty), 0) as total 
            FROM db_purchaseitems pi 
            JOIN db_purchase p ON p.id = pi.purchase_id 
            WHERE pi.item_id = $item_id AND p.status = 1
        ");
        if ($query) $summary['total_purchase'] = $query->row()->total;

        // Total Sold
        $query = $this->db->query("
            SELECT COALESCE(SUM(si.sales_qty), 0) as total 
            FROM db_salesitems si 
            JOIN db_sales s ON s.id = si.sales_id 
            WHERE si.item_id = $item_id AND s.status = 1
        ");
        if ($query) $summary['total_sold'] = $query->row()->total;

        // Opening Stock (from stock entry)
        $query = $this->db->query("
            SELECT COALESCE(SUM(qty), 0) as total 
            FROM db_stockentry 
            WHERE item_id = $item_id AND status = 1
        ");
        if ($query) $summary['opening_stock'] = $query->row()->total;

        // Sales Returns
        $query = $this->db->query("
            SELECT COALESCE(SUM(sr.return_qty), 0) as total 
            FROM db_salesitemsreturn sr 
            JOIN db_salesreturn s ON s.id = sr.return_id 
            WHERE sr.item_id = $item_id AND s.status = 1
        ");
        if ($query) $summary['total_sell_return'] = $query->row()->total;

        // Purchase Returns
        $query = $this->db->query("
            SELECT COALESCE(SUM(pr.return_qty), 0) as total 
            FROM db_purchaseitemsreturn pr 
            JOIN db_purchasereturn p ON p.id = pr.return_id 
            WHERE pr.item_id = $item_id AND p.status = 1
        ");
        if ($query) $summary['total_purchase_return'] = $query->row()->total;

        // Production Output
        $query = $this->db->query("
            SELECT COALESCE(SUM(qty), 0) as total 
            FROM inventory_movements 
            WHERE item_id = $item_id AND type = 'PRODUCTION_OUTPUT'
        ");
        if ($query) $summary['production_output'] = $query->row()->total;

        // Production Consumption
        $query = $this->db->query("
            SELECT COALESCE(SUM(qty), 0) as total 
            FROM inventory_movements 
            WHERE item_id = $item_id AND type = 'PRODUCTION_CONSUME'
        ");
        if ($query) $summary['production_consumption'] = abs($query->row()->total);

        return $summary;
    }

    // Get detailed transaction history with running balance - FIXED COLLATION ISSUE
    public function get_transaction_history($item_id, $start = 0, $length = 1000)
    {
        // Use individual queries instead of UNION to avoid collation issues
        $transactions = array();

        // 1. Sales Transactions
        $this->db->select("
            'Sell' as type,
            -si.sales_qty as quantity_change,
            si.sales_qty as absolute_quantity,
            s.sales_date as transaction_date,
            s.sales_code as reference_no,
            CONCAT(COALESCE(c.customer_name, 'Walk-in Customer'), ' (', COALESCE(c.mobile, 'N/A'), ')') as customer_supplier_info,
            s.id as source_id,
            'sales' as source_table,
            si.id as detail_id,
            s.sales_date as sort_date
        ", false);
        $this->db->from('db_salesitems si');
        $this->db->join('db_sales s', 's.id = si.sales_id', 'left');
        $this->db->join('db_customers c', 'c.id = s.customer_id', 'left');
        $this->db->where('si.item_id', $item_id);
        $this->db->where('s.status', 1);
        $this->db->order_by('s.sales_date', 'DESC');
        $this->db->order_by('si.id', 'DESC');
        $sales = $this->db->get();
        if ($sales) $transactions = array_merge($transactions, $sales->result());

        // 2. Purchase Transactions
        $this->db->select("
            'Purchase' as type,
            pi.purchase_qty as quantity_change,
            pi.purchase_qty as absolute_quantity,
            p.purchase_date as transaction_date,
            p.purchase_code as reference_no,
            CONCAT(COALESCE(sp.supplier_name, 'Unknown Supplier'), ' (', COALESCE(sp.mobile, 'N/A'), ')') as customer_supplier_info,
            p.id as source_id,
            'purchase' as source_table,
            pi.id as detail_id,
            p.purchase_date as sort_date
        ", false);
        $this->db->from('db_purchaseitems pi');
        $this->db->join('db_purchase p', 'p.id = pi.purchase_id', 'left');
        $this->db->join('db_suppliers sp', 'sp.id = p.supplier_id', 'left');
        $this->db->where('pi.item_id', $item_id);
        $this->db->where('p.status', 1);
        $this->db->order_by('p.purchase_date', 'DESC');
        $this->db->order_by('pi.id', 'DESC');
        $purchases = $this->db->get();
        if ($purchases) $transactions = array_merge($transactions, $purchases->result());

        // 3. Sales Returns
        $this->db->select("
            'Sell Return' as type,
            sr.return_qty as quantity_change,
            sr.return_qty as absolute_quantity,
            s.return_date as transaction_date,
            s.return_code as reference_no,
            CONCAT(COALESCE(c.customer_name, 'Walk-in Customer'), ' (', COALESCE(c.mobile, 'N/A'), ')') as customer_supplier_info,
            s.id as source_id,
            'sales_return' as source_table,
            sr.id as detail_id,
            s.return_date as sort_date
        ", false);
        $this->db->from('db_salesitemsreturn sr');
        $this->db->join('db_salesreturn s', 's.id = sr.return_id', 'left');
        $this->db->join('db_customers c', 'c.id = s.customer_id', 'left');
        $this->db->where('sr.item_id', $item_id);
        $this->db->where('s.status', 1);
        $this->db->order_by('s.return_date', 'DESC');
        $this->db->order_by('sr.id', 'DESC');
        $sales_returns = $this->db->get();
        if ($sales_returns) $transactions = array_merge($transactions, $sales_returns->result());

        // 4. Purchase Returns
        $this->db->select("
            'Purchase Return' as type,
            -pr.return_qty as quantity_change,
            pr.return_qty as absolute_quantity,
            p.return_date as transaction_date,
            p.return_code as reference_no,
            CONCAT(COALESCE(sp.supplier_name, 'Unknown Supplier'), ' (', COALESCE(sp.mobile, 'N/A'), ')') as customer_supplier_info,
            p.id as source_id,
            'purchase_return' as source_table,
            pr.id as detail_id,
            p.return_date as sort_date
        ", false);
        $this->db->from('db_purchaseitemsreturn pr');
        $this->db->join('db_purchasereturn p', 'p.id = pr.return_id', 'left');
        $this->db->join('db_suppliers sp', 'sp.id = p.supplier_id', 'left');
        $this->db->where('pr.item_id', $item_id);
        $this->db->where('p.status', 1);
        $this->db->order_by('p.return_date', 'DESC');
        $this->db->order_by('pr.id', 'DESC');
        $purchase_returns = $this->db->get();
        if ($purchase_returns) $transactions = array_merge($transactions, $purchase_returns->result());

        // 5. Stock Adjustments
        $this->db->select("
            CASE 
                WHEN se.qty > 0 THEN 'Stock In'
                ELSE 'Stock Out'
            END as type,
            se.qty as quantity_change,
            ABS(se.qty) as absolute_quantity,
            se.entry_date as transaction_date,
            CONCAT('STK-', se.id) as reference_no,
            COALESCE(se.note, 'Stock Adjustment') as customer_supplier_info,
            se.id as source_id,
            'stock_entry' as source_table,
            se.id as detail_id,
            se.entry_date as sort_date
        ", false);
        $this->db->from('db_stockentry se');
        $this->db->where('se.item_id', $item_id);
        $this->db->where('se.status', 1);
        $this->db->order_by('se.entry_date', 'DESC');
        $this->db->order_by('se.id', 'DESC');
        $stock_entries = $this->db->get();
        if ($stock_entries) $transactions = array_merge($transactions, $stock_entries->result());

        // 6. Production Output
        $this->db->select("
            'Production' as type,
            im.qty as quantity_change,
            im.qty as absolute_quantity,
            im.created_at as transaction_date,
            CONCAT('PROD-', COALESCE(pb.batch_code, im.reference_id)) as reference_no,
            CONCAT('Batch: ', COALESCE(pb.batch_code, 'N/A')) as customer_supplier_info,
            im.id as source_id,
            'production' as source_table,
            im.id as detail_id,
            im.created_at as sort_date
        ", false);
        $this->db->from('inventory_movements im');
        $this->db->join('production_batches pb', 'pb.id = im.reference_id', 'left');
        $this->db->where('im.item_id', $item_id);
        $this->db->where('im.type', 'PRODUCTION_OUTPUT');
        $this->db->order_by('im.created_at', 'DESC');
        $this->db->order_by('im.id', 'DESC');
        $production_output = $this->db->get();
        if ($production_output) $transactions = array_merge($transactions, $production_output->result());

        // 7. Production Consumption
        $this->db->select("
            'Production Consume' as type,
            im.qty as quantity_change,
            ABS(im.qty) as absolute_quantity,
            im.created_at as transaction_date,
            CONCAT('PROD-', COALESCE(pb.batch_code, im.reference_id)) as reference_no,
            CONCAT('Batch: ', COALESCE(pb.batch_code, 'N/A')) as customer_supplier_info,
            im.id as source_id,
            'production_consume' as source_table,
            im.id as detail_id,
            im.created_at as sort_date
        ", false);
        $this->db->from('inventory_movements im');
        $this->db->join('production_batches pb', 'pb.id = im.reference_id', 'left');
        $this->db->where('im.item_id', $item_id);
        $this->db->where('im.type', 'PRODUCTION_CONSUME');
        $this->db->order_by('im.created_at', 'DESC');
        $this->db->order_by('im.id', 'DESC');
        $production_consume = $this->db->get();
        if ($production_consume) $transactions = array_merge($transactions, $production_consume->result());

        // Sort all transactions by date
        usort($transactions, function ($a, $b) {
            return strtotime($b->sort_date) - strtotime($a->sort_date);
        });

        // Apply pagination
        $transactions = array_slice($transactions, $start, $length);

        // Calculate running balance
        return $this->calculate_running_balance($transactions, $item_id);
    }

    // Calculate running balance for transactions
    private function calculate_running_balance($transactions, $item_id)
    {
        if (empty($transactions)) {
            return array();
        }

        // Get initial stock before these transactions
        $initial_stock = $this->get_initial_stock_before_transactions($item_id, $transactions);

        $running_balance = $initial_stock;

        foreach ($transactions as $transaction) {
            $running_balance += $transaction->quantity_change;
            $transaction->new_quantity = $running_balance;
        }

        return $transactions;
    }

    private function get_initial_stock_before_transactions($item_id, $transactions)
    {
        if (empty($transactions)) {
            return 0;
        }

        // Get the earliest transaction date
        $earliest_date = null;
        foreach ($transactions as $transaction) {
            if ($earliest_date === null || $transaction->sort_date < $earliest_date) {
                $earliest_date = $transaction->sort_date;
            }
        }

        if (!$earliest_date) {
            return 0;
        }

        // Calculate stock before this date using CodeIgniter query builder
        $total = 0;

        // Sales before date
        $this->db->select('COALESCE(SUM(si.sales_qty), 0) as total', false);
        $this->db->from('db_salesitems si');
        $this->db->join('db_sales s', 's.id = si.sales_id');
        $this->db->where('si.item_id', $item_id);
        $this->db->where('s.sales_date <', $earliest_date);
        $this->db->where('s.status', 1);
        $sales = $this->db->get()->row();
        $total -= $sales->total;

        // Purchases before date
        $this->db->select('COALESCE(SUM(pi.purchase_qty), 0) as total', false);
        $this->db->from('db_purchaseitems pi');
        $this->db->join('db_purchase p', 'p.id = pi.purchase_id');
        $this->db->where('pi.item_id', $item_id);
        $this->db->where('p.purchase_date <', $earliest_date);
        $this->db->where('p.status', 1);
        $purchases = $this->db->get()->row();
        $total += $purchases->total;

        // Sales returns before date
        $this->db->select('COALESCE(SUM(sr.return_qty), 0) as total', false);
        $this->db->from('db_salesitemsreturn sr');
        $this->db->join('db_salesreturn s', 's.id = sr.return_id');
        $this->db->where('sr.item_id', $item_id);
        $this->db->where('s.return_date <', $earliest_date);
        $this->db->where('s.status', 1);
        $sales_returns = $this->db->get()->row();
        $total += $sales_returns->total;

        // Purchase returns before date
        $this->db->select('COALESCE(SUM(pr.return_qty), 0) as total', false);
        $this->db->from('db_purchaseitemsreturn pr');
        $this->db->join('db_purchasereturn p', 'p.id = pr.return_id');
        $this->db->where('pr.item_id', $item_id);
        $this->db->where('p.return_date <', $earliest_date);
        $this->db->where('p.status', 1);
        $purchase_returns = $this->db->get()->row();
        $total -= $purchase_returns->total;

        // Stock adjustments before date
        $this->db->select('COALESCE(SUM(se.qty), 0) as total', false);
        $this->db->from('db_stockentry se');
        $this->db->where('se.item_id', $item_id);
        $this->db->where('se.entry_date <', $earliest_date);
        $this->db->where('se.status', 1);
        $stock_entries = $this->db->get()->row();
        $total += $stock_entries->total;

        // Production before date
        $this->db->select('COALESCE(SUM(im.qty), 0) as total', false);
        $this->db->from('inventory_movements im');
        $this->db->where('im.item_id', $item_id);
        $this->db->where('im.created_at <', $earliest_date);
        $production = $this->db->get()->row();
        $total += $production->total;

        return $total;
    }

    // Count total transactions for pagination
    public function count_total_transactions($item_id)
    {
        $count = 0;

        // Count each type separately using CodeIgniter query builder
        $counts = [
            // Sales
            $this->db->from('db_salesitems si')
                ->join('db_sales s', 's.id = si.sales_id')
                ->where('si.item_id', $item_id)
                ->where('s.status', 1)
                ->count_all_results(),

            // Purchases
            $this->db->from('db_purchaseitems pi')
                ->join('db_purchase p', 'p.id = pi.purchase_id')
                ->where('pi.item_id', $item_id)
                ->where('p.status', 1)
                ->count_all_results(),

            // Sales Returns
            $this->db->from('db_salesitemsreturn sr')
                ->join('db_salesreturn s', 's.id = sr.return_id')
                ->where('sr.item_id', $item_id)
                ->where('s.status', 1)
                ->count_all_results(),

            // Purchase Returns
            $this->db->from('db_purchaseitemsreturn pr')
                ->join('db_purchasereturn p', 'p.id = pr.return_id')
                ->where('pr.item_id', $item_id)
                ->where('p.status', 1)
                ->count_all_results(),

            // Stock Entries
            $this->db->from('db_stockentry')
                ->where('item_id', $item_id)
                ->where('status', 1)
                ->count_all_results(),

            // Inventory Movements
            $this->db->from('inventory_movements')
                ->where('item_id', $item_id)
                ->count_all_results()
        ];

        return array_sum($counts);
    }

    // Get item details with additional info
    public function get_item_details($item_id)
    {
        $this->db->select('i.*, c.category_name, u.unit_name, b.brand_name');
        $this->db->from('db_items i');
        $this->db->join('db_category c', 'c.id = i.category_id', 'left');
        $this->db->join('db_units u', 'u.id = i.unit_id', 'left');
        $this->db->join('db_brands b', 'b.id = i.brand_id', 'left');
        $this->db->where('i.id', $item_id);

        $query = $this->db->get();
        return $query ? $query->row() : null;
    }
}
