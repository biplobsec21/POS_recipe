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

        // Opening Stock (from stock entry - only non-production entries)
        $query = $this->db->query("
        SELECT COALESCE(SUM(qty), 0) as total 
        FROM db_stockentry 
        WHERE item_id = $item_id 
        AND status = 1 
        AND (note NOT LIKE '%Production%' OR note IS NULL)
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

        // Production Output (from stock entries - including Production Output and Production Revert)
        $query = $this->db->query("
        SELECT COALESCE(SUM(se.qty), 0) as total 
        FROM db_stockentry se 
        WHERE se.item_id = $item_id 
        AND se.status = 1 
        AND (se.note LIKE '%Production Output%' OR se.note LIKE '%Production Revert%')
    ");
        if ($query) $summary['production_output'] = $query->row()->total;

        // Production Consumption (from inventory_movements only)
        $query = $this->db->query("
        SELECT COALESCE(SUM(ABS(im.qty)), 0) as total 
        FROM inventory_movements im 
        WHERE im.item_id = $item_id 
        AND im.type = 'PRODUCTION_CONSUME'
    ");
        if ($query) $summary['production_consumption'] = $query->row()->total;

        // Stock Adjustment (other adjustments excluding production)
        $query = $this->db->query("
        SELECT COALESCE(SUM(qty), 0) as total 
        FROM db_stockentry 
        WHERE item_id = $item_id 
        AND status = 1 
        AND (note NOT LIKE '%Production%' OR note IS NULL)
    ");
        if ($query) $summary['total_stock_adjustment'] = $query->row()->total;

        return $summary;
    }

    // Get transaction history with proper running balance
    public function get_transaction_history($item_id, $start = 0, $length = 1000)
    {
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
        CONCAT(s.sales_date, ' ', COALESCE(s.created_time, '00:00:00')) as sort_date
    ", false);
        $this->db->from('db_salesitems si');
        $this->db->join('db_sales s', 's.id = si.sales_id', 'left');
        $this->db->join('db_customers c', 'c.id = s.customer_id', 'left');
        $this->db->where('si.item_id', $item_id);
        $this->db->where('s.status', 1);
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
        CONCAT(p.purchase_date, ' ', COALESCE(p.created_time, '00:00:00')) as sort_date
    ", false);
        $this->db->from('db_purchaseitems pi');
        $this->db->join('db_purchase p', 'p.id = pi.purchase_id', 'left');
        $this->db->join('db_suppliers sp', 'sp.id = p.supplier_id', 'left');
        $this->db->where('pi.item_id', $item_id);
        $this->db->where('p.status', 1);
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
        CONCAT(s.return_date, ' ', COALESCE(s.created_time, '00:00:00')) as sort_date
    ", false);
        $this->db->from('db_salesitemsreturn sr');
        $this->db->join('db_salesreturn s', 's.id = sr.return_id', 'left');
        $this->db->join('db_customers c', 'c.id = s.customer_id', 'left');
        $this->db->where('sr.item_id', $item_id);
        $this->db->where('s.status', 1);
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
        CONCAT(p.return_date, ' ', COALESCE(p.created_time, '00:00:00')) as sort_date
    ", false);
        $this->db->from('db_purchaseitemsreturn pr');
        $this->db->join('db_purchasereturn p', 'p.id = pr.return_id', 'left');
        $this->db->join('db_suppliers sp', 'sp.id = p.supplier_id', 'left');
        $this->db->where('pr.item_id', $item_id);
        $this->db->where('p.status', 1);
        $purchase_returns = $this->db->get();
        if ($purchase_returns) $transactions = array_merge($transactions, $purchase_returns->result());

        // 5. Stock Adjustments (excluding production consumption duplicates)
        $this->db->select("
        CASE 
            WHEN se.note LIKE '%Opening Stock%' OR se.note = '' OR se.note IS NULL THEN 'Opening Stock'
            WHEN se.qty > 0 THEN 'Stock In'
            ELSE 'Stock Out'
        END as type,
        se.qty as quantity_change,
        ABS(se.qty) as absolute_quantity,
        se.entry_date as transaction_date,
        CONCAT('STK-', se.id) as reference_no,
        CASE 
            WHEN se.note = '' OR se.note IS NULL THEN 'Opening Stock'
            ELSE se.note
        END as customer_supplier_info,
        se.id as source_id,
        'stock_entry' as source_table,
        se.id as detail_id,
        se.entry_date as sort_date
    ", false);
        $this->db->from('db_stockentry se');
        $this->db->where('se.item_id', $item_id);
        $this->db->where('se.status', 1);
        // Exclude only production consumption duplicates (keep opening stock and production reverts)
        $this->db->where("(se.note NOT LIKE '%Production Consumption - Batch:%')");
        $stock_entries = $this->db->get();
        if ($stock_entries) $transactions = array_merge($transactions, $stock_entries->result());

        // 6. Production Consumption ONLY (no duplicates)
        $this->db->select("
        'Production Consume' as type,
        -ABS(im.qty) as quantity_change,
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
        $production_consume = $this->db->get();
        if ($production_consume) $transactions = array_merge($transactions, $production_consume->result());

        // Sort all transactions by date (oldest first for proper balance calculation)
        usort($transactions, function ($a, $b) {
            $time_a = strtotime($a->sort_date);
            $time_b = strtotime($b->sort_date);
            if ($time_a == $time_b) {
                return $a->detail_id - $b->detail_id;
            }
            return $time_a - $time_b;
        });

        // Calculate running balance from current stock backward method
        $transactions_with_balance = $this->calculate_running_balance_from_current($transactions, $item_id);

        // Now reverse to show newest first
        $transactions_with_balance = array_reverse($transactions_with_balance);

        // Apply pagination AFTER calculating balance
        return array_slice($transactions_with_balance, $start, $length);
    }

    // Calculate running balance working backward from current stock
    private function calculate_running_balance_from_current($transactions, $item_id)
    {
        if (empty($transactions)) {
            return array();
        }

        // Get current stock from database
        $this->db->select('stock');
        $this->db->from('db_items');
        $this->db->where('id', $item_id);
        $current_stock_result = $this->db->get()->row();
        $current_stock = $current_stock_result ? $current_stock_result->stock : 0;

        // Start from current stock and work forward through all transactions
        $running_balance = 0;

        foreach ($transactions as $transaction) {
            $running_balance += $transaction->quantity_change;
            $transaction->new_quantity = $running_balance;
        }

        // Now adjust all balances so the last one equals current stock
        $adjustment = $current_stock - $running_balance;

        foreach ($transactions as $transaction) {
            $transaction->new_quantity += $adjustment;
        }

        return $transactions;
    }

    // Count total transactions (excluding duplicate production entries)
    public function count_total_transactions($item_id)
    {
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

            // Stock Entries (excluding duplicate production consumption)
            $this->db->from('db_stockentry')
                ->where('item_id', $item_id)
                ->where('status', 1)
                ->where("(note NOT LIKE '%Production Consumption - Batch:%' OR note IS NULL)")
                ->count_all_results(),

            // Production Consumption only
            $this->db->from('inventory_movements')
                ->where('item_id', $item_id)
                ->where('type', 'PRODUCTION_CONSUME')
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
