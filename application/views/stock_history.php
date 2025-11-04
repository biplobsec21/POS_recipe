<!DOCTYPE html>
<html>

<head>
    <?php include "comman/code_css_datatable.php"; ?>
    <style>
        .summary-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            background: #f9f9f9;
        }

        .quantities-in {
            border-left: 4px solid #28a745;
        }

        .quantities-out {
            border-left: 4px solid #dc3545;
        }

        .totals {
            border-left: 4px solid #007bff;
        }

        .quantity-positive {
            color: #28a745;
            font-weight: bold;
        }

        .quantity-negative {
            color: #dc3545;
            font-weight: bold;
        }

        .stock-alert {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
        }

        .transaction-type-sell {
            background: #ffe6e6;
        }

        .transaction-type-purchase {
            background: #e6ffe6;
        }

        .transaction-type-return {
            background: #e6f3ff;
        }

        .transaction-type-adjustment {
            background: #fff9e6;
        }
    </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        <?php include "sidebar.php"; ?>

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    <?= $page_title; ?>
                    <small>Stock History & Ledger</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="<?php echo $base_url; ?>items">Items</a></li>
                    <li class="active"><?= $page_title; ?></li>
                </ol>
            </section>

            <!-- Main content -->
            <?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
            <input type="hidden" id='base_url' value="<?= $base_url; ?>">

            <section class="content">
                <div class="row">
                    <!-- ********** ALERT MESSAGE START******* -->
                    <?php include "comman/code_flashdata.php"; ?>
                    <!-- ********** ALERT MESSAGE END******* -->
                    <div class="col-xs-12">

                        <!-- Item Information -->
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Product Information</h3>
                                <div class="box-tools">
                                    <a href="<?php echo $base_url; ?>items/update/<?= $q_id; ?>" class="btn btn-sm btn-info">
                                        <i class="fa fa-edit"></i> Edit Item
                                    </a>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Product:</strong> <?= $item_info->item_name; ?><br>
                                        <strong>Item Code:</strong> <?= $item_info->item_code; ?><br>
                                        <strong>SKU:</strong> <?= $item_info->sku; ?>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Current Stock:</strong>
                                        <span class="label label-<?= $item_info->stock <= $item_info->alert_qty ? 'danger' : 'primary'; ?>">
                                            <?= $item_info->stock; ?> <?= $item_info->unit_name; ?>
                                        </span><br>
                                        <strong>Alert Quantity:</strong> <?= $item_info->alert_qty; ?><br>
                                        <strong>Category:</strong> <?= $item_info->category_name; ?>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Brand:</strong> <?= $item_info->brand_name; ?><br>
                                        <strong>Purchase Price:</strong> <?= number_format($item_info->purchase_price, 2); ?><br>
                                        <strong>Sales Price:</strong> <?= number_format($item_info->final_price, 2); ?>
                                    </div>
                                    <div class="col-md-3">
                                        <?php if ($item_info->stock <= $item_info->alert_qty): ?>
                                            <div class="alert alert-warning">
                                                <i class="fa fa-exclamation-triangle"></i>
                                                <strong>Low Stock Alert!</strong><br>
                                                Current stock is below alert level.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stock Summary -->
                        <div class="row">
                            <!-- Quantities In -->
                            <div class="col-md-6">
                                <div class="box box-success quantities-in">
                                    <div class="box-header with-border">
                                        <h3 class="box-title"><i class="fa fa-arrow-down"></i> Quantities In</h3>
                                    </div>
                                    <div class="box-body">
                                        <p>Total Purchase: <span class="quantity-positive"><?= $stock_summary['total_purchase']; ?> <?= $item_info->unit_name; ?></span></p>
                                        <p>Opening Stock: <span class="quantity-positive"><?= $stock_summary['opening_stock']; ?> <?= $item_info->unit_name; ?></span></p>
                                        <p>Total Sell Return: <span class="quantity-positive"><?= $stock_summary['total_sell_return']; ?> <?= $item_info->unit_name; ?></span></p>
                                        <p>Production Output: <span class="quantity-positive"><?= $stock_summary['production_output']; ?> <?= $item_info->unit_name; ?></span></p>
                                        <p>Stock Transfers (In): <span class="quantity-positive"><?= $stock_summary['stock_transfers_in']; ?> <?= $item_info->unit_name; ?></span></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Quantities Out -->
                            <div class="col-md-6">
                                <div class="box box-danger quantities-out">
                                    <div class="box-header with-border">
                                        <h3 class="box-title"><i class="fa fa-arrow-up"></i> Quantities Out</h3>
                                    </div>
                                    <div class="box-body">
                                        <p>Total Sold: <span class="quantity-negative"><?= $stock_summary['total_sold']; ?> <?= $item_info->unit_name; ?></span></p>
                                        <p>Total Stock Adjustment: <span class="quantity-negative"><?= $stock_summary['total_stock_adjustment']; ?> <?= $item_info->unit_name; ?></span></p>
                                        <p>Total Purchase Return: <span class="quantity-negative"><?= $stock_summary['total_purchase_return']; ?> <?= $item_info->unit_name; ?></span></p>
                                        <p>Production Consumption: <span class="quantity-negative"><?= $stock_summary['production_consumption']; ?> <?= $item_info->unit_name; ?></span></p>
                                        <p>Stock Transfers (Out): <span class="quantity-negative"><?= $stock_summary['stock_transfers_out']; ?> <?= $item_info->unit_name; ?></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Current Stock Total -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="box box-primary totals <?= $item_info->stock <= $item_info->alert_qty ? 'stock-alert' : ''; ?>">
                                    <div class="box-body text-center">
                                        <h2>
                                            <i class="fa fa-cubes"></i>
                                            Current Stock:
                                            <span class="text-<?= $item_info->stock <= $item_info->alert_qty ? 'danger' : 'primary'; ?>">
                                                <?= $stock_summary['current_stock']; ?> <?= $item_info->unit_name; ?>
                                            </span>
                                        </h2>
                                        <?php if ($item_info->stock <= $item_info->alert_qty): ?>
                                            <p class="text-danger">
                                                <i class="fa fa-exclamation-circle"></i>
                                                Stock is below alert level (<?= $item_info->alert_qty; ?> <?= $item_info->unit_name; ?>)
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Transaction History -->
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">Transaction History</h3>
                                <div class="box-tools">
                                    <button class="btn btn-sm btn-default" onclick="refreshTable()">
                                        <i class="fa fa-refresh"></i> Refresh
                                    </button>
                                </div>
                            </div>
                            <div class="box-body">
                                <table id="transaction_table" class="table table-bordered table-striped" width="100%">
                                    <thead class="bg-primary">
                                        <tr>
                                            <th>Type</th>
                                            <th>Quantity Change</th>
                                            <th>New Quantity</th>
                                            <th>Date & Time</th>
                                            <th>Reference No</th>
                                            <th>Customer/Supplier Information</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($transactions as $transaction): ?>
                                            <tr class="transaction-type-<?= strtolower(str_replace(' ', '-', $transaction->type)); ?>">
                                                <td>
                                                    <span class="label 
                                                <?= in_array($transaction->type, ['Purchase', 'Sell Return', 'Stock In', 'Production']) ? 'label-success' : ''; ?>
                                                <?= in_array($transaction->type, ['Sell', 'Purchase Return', 'Stock Out', 'Production Consume']) ? 'label-danger' : ''; ?>
                                                <?= $transaction->type == 'Adjustment' ? 'label-warning' : ''; ?>
                                            ">
                                                        <?= $transaction->type; ?>
                                                    </span>
                                                </td>
                                                <td class="<?= $transaction->quantity_change >= 0 ? 'quantity-positive' : 'quantity-negative'; ?>">
                                                    <?= $transaction->quantity_change >= 0 ? '+' : ''; ?><?= $transaction->quantity_change; ?>
                                                </td>
                                                <td><strong><?= $transaction->new_quantity; ?></strong></td>
                                                <td><?= date('d-m-Y h:i A', strtotime($transaction->transaction_date)); ?></td>
                                                <td><code><?= $transaction->reference_no; ?></code></td>
                                                <td><?= $transaction->customer_supplier_info; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- /.content -->
            <?= form_close(); ?>
        </div>
        <!-- /.content-wrapper -->
        <?php include "footer.php"; ?>
        <!-- Add the sidebar's background. This div must be placed
             immediately after the control sidebar -->
        <div class="control-sidebar-bg"></div>
    </div>
    <!-- ./wrapper -->

    <!-- SOUND CODE -->
    <?php include "comman/code_js_sound.php"; ?>
    <!-- TABLES CODE -->
    <?php include "comman/code_js_datatable.php"; ?>

    <script type="text/javascript">
        function load_datatable() {
            //datatables
            var table = $('#transaction_table').DataTable({

                /* FOR EXPORT BUTTONS START*/
                dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>tip',
                buttons: {
                    buttons: [{
                            extend: 'copy',
                            className: 'btn bg-teal color-palette btn-flat',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5]
                            }
                        },
                        {
                            extend: 'excel',
                            className: 'btn bg-teal color-palette btn-flat',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5]
                            }
                        },
                        {
                            extend: 'pdf',
                            className: 'btn bg-teal color-palette btn-flat',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5]
                            }
                        },
                        {
                            extend: 'print',
                            className: 'btn bg-teal color-palette btn-flat',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5]
                            }
                        },
                        {
                            extend: 'csv',
                            className: 'btn bg-teal color-palette btn-flat',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5]
                            }
                        },
                        {
                            extend: 'colvis',
                            className: 'btn bg-teal color-palette btn-flat',
                            text: 'Columns'
                        },
                    ]
                },
                /* FOR EXPORT BUTTONS END */

                "processing": true,
                "serverSide": false, // Make sure this is false for client-side processing
                "ordering": true,
                "order": [
                    [3, 'desc']
                ], // Order by date column (4th column) descending
                "responsive": true,
                "pageLength": 25,
                "lengthMenu": [10, 25, 50, 100, 500, 1000],
                language: {
                    processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>'
                },
                // Remove the ajax section since we're loading data directly from PHP
                "data": <?= json_encode($transactions); ?>, // Load data directly from PHP
                "columns": [{
                        "data": "type",
                        "render": function(data, type, row) {
                            var labelClass = 'label-default';
                            if (['Purchase', 'Sell Return', 'Stock In', 'Production'].includes(data)) {
                                labelClass = 'label-success';
                            } else if (['Sell', 'Purchase Return', 'Stock Out', 'Production Consume'].includes(data)) {
                                labelClass = 'label-danger';
                            } else if (data == 'Adjustment') {
                                labelClass = 'label-warning';
                            }
                            return '<span class="label ' + labelClass + '">' + data + '</span>';
                        }
                    },
                    {
                        "data": "quantity_change",
                        "render": function(data, type, row) {
                            var className = data >= 0 ? 'quantity-positive' : 'quantity-negative';
                            var sign = data >= 0 ? '+' : '';
                            return '<span class="' + className + '">' + sign + parseFloat(data).toFixed(2) + '</span>';
                        }
                    },
                    {
                        "data": "new_quantity",
                        "render": function(data, type, row) {
                            return '<strong>' + parseFloat(data).toFixed(2) + '</strong>';
                        }
                    },
                    {
                        "data": "transaction_date"
                    },
                    {
                        "data": "reference_no",
                        "render": function(data, type, row) {
                            return '<code>' + data + '</code>';
                        }
                    },
                    {
                        "data": "customer_supplier_info"
                    }
                ],
                "columnDefs": [{
                    "targets": [0, 1, 2, 3, 4, 5],
                    "orderable": true,
                }],
            });
            new $.fn.dataTable.FixedHeader(table);
        }

        $(document).ready(function() {
            //datatables
            load_datatable();
        });

        // Refresh table function - now we need to reload the page since we're not using AJAX
        window.refreshTable = function() {
            location.reload();
        }
    </script>

    <!-- Make sidebar menu highlighter/selector -->
    <script>
        $(".<?php echo basename(__FILE__, '.php'); ?>-active-li").addClass("active");
    </script>

</body>

</html>