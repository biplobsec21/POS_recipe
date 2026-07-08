<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Damage_model extends CI_Model
{
    // ---------------------------------------------------------------
    // DataTables config  (mirrors Sales_model pattern)
    // ---------------------------------------------------------------
    var $table         = 'db_damage as a';
    var $column_order  = array(
        'a.id', 'a.damage_date', 'a.damage_code', 'a.damage_type',
        null,                        // items (not sortable)
        'w.warehouse_name',
        'a.total_qty', 'a.total_value', 'a.status', 'a.created_by',
    );
    var $column_search = array(
        'a.damage_code', 'a.damage_date', 'a.damage_type',
        'a.status', 'a.reason', 'w.warehouse_name',
    );
    var $order = array('a.id' => 'desc');

    public function __construct()
    {
        parent::__construct();
    }

    // ---------------------------------------------------------------
    // DataTables
    // ---------------------------------------------------------------

    private function _get_datatables_query()
    {
        $this->db->select('a.id, a.damage_date, a.damage_code, a.damage_type,
                           a.total_qty, a.total_value, a.status,
                           a.reason, a.note, a.created_by,
                           w.warehouse_name,
                           u.username AS created_by_name');
        $this->db->from($this->table);
        $this->db->join('db_warehouse as w', 'w.id = a.warehouse_id', 'left');
        $this->db->join('db_users as u',     'u.id = a.created_by',   'left');

        // Filters from POST
        $damage_type = $this->input->post('filter_damage_type');
        $status      = $this->input->post('filter_status');
        $from_date   = system_fromatted_date($this->input->post('damage_from_date'));
        $to_date     = system_fromatted_date($this->input->post('damage_to_date'));

        if (!empty($damage_type)) {
            $this->db->where('a.damage_type', $damage_type);
        }
        if (!empty($status)) {
            $this->db->where('a.status', $status);
        }
        if ($from_date !== '1970-01-01') {
            $this->db->where('a.damage_date >=', $from_date);
        }
        if ($to_date !== '1970-01-01') {
            $this->db->where('a.damage_date <=', $to_date);
        }

        // Search
        $i = 0;
        foreach ($this->column_search as $col) {
            if (!empty($_POST['search']['value'])) {
                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($col, $_POST['search']['value']);
                } else {
                    $this->db->or_like($col, $_POST['search']['value']);
                }
                if ((int)(count($this->column_search) - 1) === $i) {
                    $this->db->group_end();
                }
            }
            $i++;
        }

        // Order
        if (isset($_POST['order'])) {
            $col_idx   = (int) $_POST['order']['0']['column'];
            $order_col = isset($this->column_order[$col_idx]) && $this->column_order[$col_idx]
                         ? $this->column_order[$col_idx] : 'a.id';
            $order_dir = strtoupper($_POST['order']['0']['dir']) === 'ASC' ? 'ASC' : 'DESC';
            $this->db->order_by($order_col, $order_dir);
        } else {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    public function get_datatables()
    {
        $this->_get_datatables_query();
        if (isset($_POST['length']) && (int) $_POST['length'] !== -1) {
            $this->db->limit((int) $_POST['length'], (int) $_POST['start']);
        }
        return $this->db->get()->result();
    }

    public function count_filtered()
    {
        $this->_get_datatables_query();
        return $this->db->get()->num_rows();
    }

    public function count_all()
    {
        return (int) $this->db->select('COUNT(*) as total')->from($this->table)->get()->row()->total;
    }

    // ---------------------------------------------------------------
    // Batch-fetch items for list display  (mirrors get_sales_items_batch)
    // ---------------------------------------------------------------

    public function get_damage_items_batch(array $damage_ids)
    {
        if (empty($damage_ids)) {
            return array();
        }
        $ids = implode(',', array_map('intval', $damage_ids));
        $query = $this->db->query("
            SELECT di.damage_id,
                   GROUP_CONCAT(CONCAT(CAST(di.damage_qty AS UNSIGNED), ' ', i.item_name) SEPARATOR ', ') AS items
            FROM   db_damageitems di
            JOIN   db_items i ON i.id = di.item_id
            WHERE  di.damage_id IN ({$ids})
            GROUP  BY di.damage_id
        ");
        $map = array();
        foreach ($query->result() as $row) {
            $map[$row->damage_id] = $row->items;
        }
        return $map;
    }

    // ---------------------------------------------------------------
    // Single record / detail queries
    // ---------------------------------------------------------------

    public function get_damage($id)
    {
        return $this->db
            ->select('d.*, w.warehouse_name, u.username AS created_by_name, au.username AS approved_by_name')
            ->from('db_damage d')
            ->join('db_warehouse w',  'w.id = d.warehouse_id', 'left')
            ->join('db_users u',      'u.id = d.created_by',   'left')
            ->join('db_users au',     'au.id = d.approved_by', 'left')
            ->where('d.id', (int) $id)
            ->get()->row();
    }

    public function get_damage_items($id)
    {
        return $this->db
            ->select('di.*, i.item_code, i.item_name, i.stock AS current_stock')
            ->from('db_damageitems di')
            ->join('db_items i', 'i.id = di.item_id', 'left')
            ->where('di.damage_id', (int) $id)
            ->get()->result();
    }

    // ---------------------------------------------------------------
    // XSS filter  (mirrors Sales_model)
    // ---------------------------------------------------------------

    public function xss_html_filter($input)
    {
        return $this->security->xss_clean(html_escape($input));
    }

    // ---------------------------------------------------------------
    // Item search  (mirrors sales/search_item — uses same items endpoint)
    // ---------------------------------------------------------------

    public function search_item($q)
    {
        $json = array();
        $q1 = $this->db->query(
            "SELECT id, item_name FROM db_items
             WHERE status = 1
               AND (UPPER(item_name) LIKE UPPER('%{$q}%') OR UPPER(item_code) LIKE UPPER('%{$q}%'))"
        );
        foreach ($q1->result() as $row) {
            $json[] = array('id' => (int) $row->id, 'text' => $row->item_name);
        }
        return json_encode($json);
    }

    // ---------------------------------------------------------------
    // Build a single HTML table row for the damage entry form
    // (mirrors Sales_model::get_items_info + return_row_with_data)
    // ---------------------------------------------------------------

    public function get_items_info($rowcount, $item_id)
    {
        $q1 = $this->db->select('*')->from('db_items')->where('id', (int) $item_id)->get();
        if ($q1->num_rows() === 0) {
            return '';
        }
        $item = $q1->row();

        $info = array(
            'item_id'          => $item->id,
            'item_name'        => $item->item_name,
            'item_damage_qty'  => 1,
            'item_available_qty' => (float) $item->stock,
            'unit_cost'        => (float) $item->price,     // purchase / cost price
            'total_value'      => (float) $item->price,     // 1 × unit_cost
            'item_reason'      => '',
        );

        $this->return_row_with_data($rowcount, $info);
    }

    // ---------------------------------------------------------------
    // Load existing damage items for edit mode
    // (mirrors Sales_model::return_sales_list)
    // ---------------------------------------------------------------

    public function return_damage_list($damage_id)
    {
        $rows = $this->db
            ->select('di.*, i.item_name, i.stock AS current_stock, i.price AS purchase_price')
            ->from('db_damageitems di')
            ->join('db_items i', 'i.id = di.item_id', 'left')
            ->where('di.damage_id', (int) $damage_id)
            ->get()->result();

        $rowcount = 1;
        $result   = '';
        foreach ($rows as $res) {
            $info = array(
                'item_id'            => $res->item_id,
                'item_name'          => $res->item_name,
                'item_damage_qty'    => (float) $res->damage_qty,
                'item_available_qty' => (float) $res->current_stock + (float) $res->damage_qty,
                'unit_cost'          => (float) $res->unit_cost,
                'total_value'        => (float) $res->total_value,
                'item_reason'        => $res->reason ?? '',
            );
            ob_start();
            $this->return_row_with_data($rowcount++, $info);
            $result .= ob_get_clean();
        }
        return $result;
    }

    // ---------------------------------------------------------------
    // Render one HTML <tr> for the damage items table
    // ---------------------------------------------------------------

    public function return_row_with_data($rowcount, $info)
    {
        extract($info);
?>
        <tr id="row_<?= $rowcount; ?>" data-row="<?= $rowcount; ?>">

            <!-- Item Name -->
            <td id="td_<?= $rowcount; ?>_1">
                <label class="form-control" style="height:auto;">
                    <span id="td_data_<?= $rowcount; ?>_1"><?= htmlspecialchars($item_name); ?></span>
                </label>
            </td>

            <!-- Quantity -->
            <td id="td_<?= $rowcount; ?>_3">
                <div class="input-group">
                    <span class="input-group-btn">
                        <button onclick="damage_decrement_qty(<?= $rowcount; ?>)" type="button"
                                class="btn btn-default btn-flat">
                            <i class="fa fa-minus text-danger"></i>
                        </button>
                    </span>
                    <input type="text" value="<?= $item_damage_qty; ?>"
                           class="form-control no-padding text-center"
                           onchange="damage_qty_input(<?= $rowcount; ?>)"
                           id="td_data_<?= $rowcount; ?>_3"
                           name="td_data_<?= $rowcount; ?>_3">
                    <span class="input-group-btn">
                        <button onclick="damage_increment_qty(<?= $rowcount; ?>)" type="button"
                                class="btn btn-default btn-flat">
                            <i class="fa fa-plus text-success"></i>
                        </button>
                    </span>
                </div>
            </td>

            <!-- Unit Cost -->
            <td id="td_<?= $rowcount; ?>_10">
                <input type="text"
                       name="td_data_<?= $rowcount; ?>_10"
                       id="td_data_<?= $rowcount; ?>_10"
                       class="form-control text-right no-padding only_currency text-center"
                       onkeyup="damage_recalc(<?= $rowcount; ?>)"
                       value="<?= number_format($unit_cost, 2, '.', ''); ?>">
            </td>

            <!-- Total Value (readonly) -->
            <td id="td_<?= $rowcount; ?>_9">
                <input type="text"
                       name="td_data_<?= $rowcount; ?>_9"
                       id="td_data_<?= $rowcount; ?>_9"
                       class="form-control text-right no-padding only_currency text-center"
                       style="border-color:#f39c12;"
                       readonly
                       value="<?= number_format($total_value, 2, '.', ''); ?>">
            </td>

            <!-- Item Reason -->
            <td id="td_<?= $rowcount; ?>_reason">
                <input type="text"
                       name="td_data_<?= $rowcount; ?>_reason"
                       id="td_data_<?= $rowcount; ?>_reason"
                       class="form-control"
                       placeholder="Reason..."
                       value="<?= htmlspecialchars($item_reason); ?>">
            </td>

            <!-- Remove -->
            <td id="td_<?= $rowcount; ?>_16" style="text-align:center;">
                <a class="fa fa-fw fa-minus-square text-red"
                   style="cursor:pointer;font-size:34px;"
                   onclick="damage_removerow(<?= $rowcount; ?>)"
                   title="Remove"></a>
            </td>

            <!-- Hidden fields -->
            <input type="hidden" id="tr_item_id_<?= $rowcount; ?>"
                   name="tr_item_id_<?= $rowcount; ?>"
                   value="<?= (int) $item_id; ?>">
            <input type="hidden" id="tr_available_qty_<?= $rowcount; ?>"
                   value="<?= $item_available_qty; ?>">
        </tr>
<?php
    }

    // ---------------------------------------------------------------
    // Save / Update  (mirrors Sales_model::verify_save_and_update)
    // ---------------------------------------------------------------

    public function verify_save_and_update()
    {
        $this->db->trans_begin();

        $post = $this->xss_html_filter(array_merge($this->data, $_POST, $_GET));
        extract($post);

        $damage_date = date('Y-m-d', strtotime($damage_date));
        $rowcount    = (int) $rowcount;
        $damage_id   = isset($damage_id) ? (int) $damage_id : 0;

        // Block edits on approved records
        if ($damage_id > 0) {
            $existing = $this->db->select('status')->from('db_damage')->where('id', $damage_id)->get()->row();
            if (!empty($existing) && $existing->status === 'approved') {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', 'Approved damage records cannot be edited.');
                return 'approved_record';
            }
        }

        // Generate damage code on new records
        if ($command === 'save') {
            $this->db->query('ALTER TABLE db_damage AUTO_INCREMENT = 1');
            $next_id     = (int) $this->db->query('SELECT COALESCE(MAX(id),0)+1 AS nid FROM db_damage')->row()->nid;
            $damage_code = 'DMG-' . date('Ymd') . '-' . str_pad($next_id, 4, '0', STR_PAD_LEFT);
            // Allow manual override
            if (!empty($damage_code_input)) {
                $damage_code = trim($damage_code_input);
            }
        }

        // Collect item rows
        $item_rows   = array();
        $total_qty   = 0.0;
        $total_value = 0.0;

        for ($i = 1; $i <= $rowcount; $i++) {
            if (empty($_REQUEST['tr_item_id_' . $i])) {
                continue;
            }
            $item_id    = (int)   $this->xss_html_filter(trim($_REQUEST['tr_item_id_' . $i]));
            $damage_qty = (float) $this->xss_html_filter(trim($_REQUEST['td_data_' . $i . '_3']));
            $unit_cost  = (float) $this->xss_html_filter(trim($_REQUEST['td_data_' . $i . '_10']));
            $line_total = (float) $this->xss_html_filter(trim($_REQUEST['td_data_' . $i . '_9']));
            $item_rsn   =         $this->xss_html_filter(trim($_REQUEST['td_data_' . $i . '_reason'] ?? ''));

            if ($item_id <= 0 || $damage_qty <= 0) {
                continue;
            }

            $item_rows[] = array(
                'item_id'     => $item_id,
                'damage_qty'  => $damage_qty,
                'unit_cost'   => $unit_cost,
                'total_value' => $line_total,
                'reason'      => $item_rsn,
                'status'      => 'draft',
            );
            $total_qty   += $damage_qty;
            $total_value += $line_total;
        }

        if (empty($item_rows)) {
            $this->db->trans_rollback();
            return 'Please add at least one item.';
        }

        $damage_entry = array(
            'damage_date'  => $damage_date,
            'warehouse_id' => !empty($warehouse_id) ? (int) $warehouse_id : null,
            'company_id'   => !empty($company_id)   ? (int) $company_id   : null,
            'damage_type'  => !empty($damage_type)  ? $damage_type : 'general',
            'reason'       => !empty($reason)        ? $reason : null,
            'note'         => !empty($note)          ? $note   : null,
            'status'       => 'draft',
            'total_qty'    => $total_qty,
            'total_value'  => $total_value,
            'system_ip'    => $this->input->ip_address(),
            'system_name'  => gethostbyaddr($this->input->ip_address()),
        );

        if ($command === 'save') {
            $damage_entry['damage_code']  = $damage_code;
            $damage_entry['created_date'] = $CUR_DATE;
            $damage_entry['created_time'] = $CUR_TIME;
            $damage_entry['created_by']   = (int) $this->session->userdata('inv_userid');

            $this->db->insert('db_damage', $damage_entry);
            $damage_id = $this->db->insert_id();
        } else {
            // update
            $this->db->where('id', $damage_id)->update('db_damage', $damage_entry);
            $this->db->where('damage_id', $damage_id)->delete('db_damageitems');
        }

        // Insert item rows
        foreach ($item_rows as $row) {
            // Capture stock_before at save time
            $stock_now = (float) $this->db->select('stock')->from('db_items')->where('id', $row['item_id'])->get()->row()->stock;

            $row['damage_id']   = $damage_id;
            $row['stock_before'] = $stock_now;
            $row['created_at']  = date('Y-m-d H:i:s');
            $this->db->insert('db_damageitems', $row);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return 'failed';
        }

        $this->db->trans_commit();
        $this->session->set_flashdata('success', 'Damage record saved successfully!');
        return 'success<<<###>>>' . $damage_id;
    }

    // ---------------------------------------------------------------
    // Approve — deducts stock  (unchanged logic, kept clean)
    // ---------------------------------------------------------------

    public function approve_damage($damage_id)
    {
        $this->db->trans_begin();

        $damage = $this->db->select('*')->from('db_damage')->where('id', (int) $damage_id)->get()->row();
        if (empty($damage) || $damage->status === 'approved') {
            $this->db->trans_rollback();
            return 'failed';
        }

        $items = $this->db->select('*')->from('db_damageitems')->where('damage_id', (int) $damage_id)->get()->result();
        if (empty($items)) {
            $this->db->trans_rollback();
            return 'failed';
        }

        foreach ($items as $row) {
            $item = $this->db->select('id, stock')->from('db_items')->where('id', $row->item_id)->get()->row();
            if (empty($item)) {
                continue;
            }
            if ((float) $item->stock < (float) $row->damage_qty) {
                $this->db->trans_rollback();
                return 'insufficient_stock';
            }
            $new_stock = (float) $item->stock - (float) $row->damage_qty;
            $this->db->where('id', $item->id)->update('db_items', array('stock' => $new_stock));

            // Record inventory movement (damage_id column added via migration)
            $this->db->insert('inventory_movements', array(
                'item_id'     => $item->id,
                'qty'         => -(float) $row->damage_qty,
                'type'        => 'damage',
                'reference_id'=> (int) $damage_id,
                'damage_id'   => (int) $damage_id,
                'created_by'  => (int) $this->session->userdata('inv_userid'),
                'created_at'  => date('Y-m-d H:i:s'),
            ));
        }

        $this->db->where('id', (int) $damage_id)->update('db_damage', array(
            'status'      => 'approved',
            'approved_by' => (int) $this->session->userdata('inv_userid'),
            'approved_at' => date('Y-m-d H:i:s'),
        ));
        $this->db->where('damage_id', (int) $damage_id)->update('db_damageitems', array('status' => 'approved'));

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return 'failed';
        }

        $this->db->trans_commit();
        return 'success';
    }

    // ---------------------------------------------------------------
    // Delete single
    // ---------------------------------------------------------------

    public function delete_damage($damage_id)
    {
        $this->db->trans_begin();
        $this->db->where('damage_id', (int) $damage_id)->delete('db_damageitems');
        $this->db->where('id',        (int) $damage_id)->delete('db_damage');

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return 'failed';
        }
        $this->db->trans_commit();
        return 'success';
    }

    // ---------------------------------------------------------------
    // Delete multiple
    // ---------------------------------------------------------------

    public function multi_delete($ids)
    {
        $this->db->trans_begin();
        $this->db->query("DELETE FROM db_damageitems WHERE damage_id IN ({$ids})");
        $this->db->query("DELETE FROM db_damage WHERE id IN ({$ids})");

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return 'failed';
        }
        $this->db->trans_commit();
        return 'success';
    }
}
