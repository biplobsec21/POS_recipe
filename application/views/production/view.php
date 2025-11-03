<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $page_title; ?></title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.7 -->
    <!-- CSRF Protection -->
    <meta name="csrf-token" content="<?= $this->security->get_csrf_hash(); ?>">
    <meta name="csrf-token-name" content="<?= $this->security->get_csrf_token_name(); ?>">


    <?php $this->load->view('comman/code_css_datatable'); ?>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    <style>
        .info-box-content {
            padding: 10px;
        }

        .batch-header {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .detail-section {
            margin-bottom: 30px;
        }

        .status-badge {
            font-size: 14px;
            padding: 8px 15px;
        }

        .cost-highlight {
            font-size: 18px;
            font-weight: bold;
            color: #0073b7;
        }
    </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

        <?php $this->load->view('sidebar'); ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>
                    <?= $page_title; ?>
                    <small>Production Batch Details</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="<?= base_url('production'); ?>">Production Batches</a></li>
                    <li class="active"><?= $page_title; ?></li>
                </ol>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="row">
                    <!-- ********** ALERT MESSAGE START******* -->
                    <?php $this->load->view('comman/code_flashdata'); ?>
                    <!-- ********** ALERT MESSAGE END******* -->

                    <div class="col-md-12">
                        <!-- Batch Header Information -->
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Batch Information</h3>
                                <div class="box-tools pull-right">
                                    <?php if ($production->status == 'Draft' && $CI->permissions('production_edit')): ?>
                                        <a href="<?= base_url('production/edit/' . $production->id); ?>" class="btn btn-primary btn-sm">
                                            <i class="fa fa-edit"></i> Edit
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($production->status == 'Draft' && $CI->permissions('production_approve')): ?>
                                        <button onclick="approveProduction(<?= $production->id; ?>)" class="btn btn-success btn-sm">
                                            <i class="fa fa-check"></i> Approve
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($CI->permissions('production_delete') && $production->status != 'Approved'): ?>
                                        <button onclick="deleteProduction(<?= $production->id; ?>)" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                    <?php endif; ?>
                                    <a href="<?= base_url('production'); ?>" class="btn btn-default btn-sm">
                                        <i class="fa fa-arrow-left"></i> Back to List
                                    </a>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="30%">Batch Code</th>
                                                <td><?= $production->batch_code; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Recipe Name</th>
                                                <td><?= $production->recipe_name; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Batch Quantity</th>
                                                <td><?= $production->batch_quantity; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Output Product</th>
                                                <td><?= $production->output_product_name ?? 'N/A'; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Yield Quantity</th>
                                                <td><?= $production->yield_quantity; ?> per batch</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="30%">Status</th>
                                                <td>
                                                    <span class="label label-<?= $production->status == 'Approved' ? 'success' : ($production->status == 'Draft' ? 'warning' : 'default'); ?> status-badge">
                                                        <?= $production->status; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Total Output</th>
                                                <td><strong><?= ($production->yield_quantity * $production->batch_quantity); ?> units</strong></td>
                                            </tr>
                                            <?php if ($production->status == 'Approved'): ?>
                                                <tr>
                                                    <th>Total Cost</th>
                                                    <td class="cost-highlight"><?= $currency . number_format($production->total_cost, 2); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Cost Per Unit</th>
                                                    <td class="cost-highlight"><?= $currency . number_format($production->cost_per_unit, 2); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Approved By</th>
                                                    <td><?= $approved_by_username; ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Approved At</th>
                                                    <td><?= show_date($production->approved_at); ?></td>
                                                </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <th>Created By</th>
                                                <td><?= $created_by_username ?></td>
                                            </tr>
                                            <tr>
                                                <th>Created At</th>
                                                <td><?= show_date($production->created_at); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <?php if (!empty($production->notes)): ?>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h4>Notes</h4>
                                            <div class="well"><?= nl2br(htmlspecialchars($production->notes)); ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Recipe Ingredients Section -->
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">Recipe Ingredients</h3>
                            </div>
                            <div class="box-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr class="bg-info">
                                                <th>#</th>
                                                <th>Ingredient Name</th>
                                                <th>Quantity Per Batch</th>
                                                <th>Total Required</th>
                                                <th>Unit</th>
                                                <th>Unit Cost</th>
                                                <th>Total Cost</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $total_ingredient_cost = 0;
                                            if (!empty($recipe_items)):
                                                foreach ($recipe_items as $index => $item):
                                                    $item_details = $this->Items_model->get_item_details_by_id($item->item_id);
                                                    $total_required = $item->quantity * $production->batch_quantity;
                                                    $item_total_cost = $item_details->purchase_price * $total_required;
                                                    $total_ingredient_cost += $item_total_cost;
                                            ?>
                                                    <tr>
                                                        <td><?= $index + 1; ?></td>
                                                        <td><?= $item_details->item_name; ?></td>
                                                        <td><?= number_format($item->quantity, 2); ?></td>
                                                        <td><?= number_format($total_required, 2); ?></td>
                                                        <td><?= $item_details->unit_name; ?></td>
                                                        <td class="text-right"><?= $currency . number_format($item_details->purchase_price, 2); ?></td>
                                                        <td class="text-right"><?= $currency . number_format($item_total_cost, 2); ?></td>
                                                    </tr>
                                                <?php
                                                endforeach;
                                            else:
                                                ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No ingredients found for this recipe.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                        <?php if (!empty($recipe_items)): ?>
                                            <tfoot>
                                                <tr class="bg-success">
                                                    <td colspan="6" class="text-right"><strong>Total Ingredients Cost:</strong></td>
                                                    <td class="text-right"><strong><?= $currency . number_format($total_ingredient_cost, 2); ?></strong></td>
                                                </tr>
                                            </tfoot>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Output Summary -->
                        <div class="box box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">Production Output Summary</h3>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-green"><i class="fa fa-cubes"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Batch Quantity</span>
                                                <span class="info-box-number"><?= $production->batch_quantity; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-blue"><i class="fa fa-industry"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Yield Per Batch</span>
                                                <span class="info-box-number"><?= $production->yield_quantity; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-purple"><i class="fa fa-line-chart"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total Output</span>
                                                <span class="info-box-number"><?= $production->yield_quantity * $production->batch_quantity; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($production->status == 'Approved'): ?>
                                    <div class="row">
                                        <div class="col-md-4 col-sm-6 col-xs-12">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-red"><i class="fa fa-money"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total Cost</span>
                                                    <span class="info-box-number"><?= $currency . number_format($production->total_cost, 2); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-6 col-xs-12">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-yellow"><i class="fa fa-calculator"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Cost Per Unit</span>
                                                    <span class="info-box-number"><?= $currency . number_format($production->cost_per_unit, 2); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-6 col-xs-12">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-teal"><i class="fa fa-percent"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Efficiency</span>
                                                    <span class="info-box-number">
                                                        <?php
                                                        $efficiency = ($total_ingredient_cost > 0) ? (($production->total_cost / $total_ingredient_cost) * 100) : 100;
                                                        echo number_format($efficiency, 2) . '%';
                                                        ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Inventory Movements (Only for Approved batches) -->
                        <?php if ($production->status == 'Approved'): ?>
                            <div class="box box-warning">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Inventory Movements</h3>
                                </div>
                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h4>Consumed Ingredients</h4>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped">
                                                    <thead>
                                                        <tr class="bg-warning">
                                                            <th>Item Name</th>
                                                            <th>Quantity Consumed</th>
                                                            <th>Movement Type</th>
                                                            <th>Date</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (!empty($inventory_movements)): ?>
                                                            <?php foreach ($inventory_movements as $movement): ?>
                                                                <?php
                                                                $item_details = $this->Items_model->get_item_details_by_id($movement->item_id);
                                                                ?>
                                                                <tr>
                                                                    <td><?= $item_details->item_name ?? 'N/A'; ?></td>
                                                                    <td class="text-danger"><?= number_format($movement->qty, 2); ?></td>
                                                                    <td><span class="label label-warning"><?= $movement->type; ?></span></td>
                                                                    <td><?= show_date($movement->created_at); ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <tr>
                                                                <td colspan="4" class="text-center">No consumption records found.</td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h4>Produced Output</h4>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped">
                                                    <thead>
                                                        <tr class="bg-success">
                                                            <th>Item Name</th>
                                                            <th>Quantity Produced</th>
                                                            <th>Movement Type</th>
                                                            <th>Date</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (!empty($output_movements)): ?>
                                                            <?php foreach ($output_movements as $movement): ?>
                                                                <?php
                                                                $item_details = $this->Items_model->get_item_details_by_id($movement->item_id);
                                                                ?>
                                                                <tr>
                                                                    <td><?= $item_details->item_name ?? 'N/A'; ?></td>
                                                                    <td class="text-success">+<?= number_format($movement->qty, 2); ?></td>
                                                                    <td><span class="label label-success"><?= $movement->type; ?></span></td>
                                                                    <td><?= show_date($movement->created_at); ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <tr>
                                                                <td colspan="4" class="text-center">No output records found.</td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <?php $this->load->view('footer'); ?>

        <div class="control-sidebar-bg"></div>
    </div>
    <!-- ./wrapper -->


    <script>
        function approveProduction(id) {
            if (confirm('Are you sure you want to approve this production batch? This action cannot be undone.')) {
                // Get CSRF token from meta tag (make sure you have this in your layout)
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                var csrfName = $('meta[name="csrf-token-name"]').attr('content') || 'csrf_test_name';

                $.ajax({
                    url: '<?= base_url('production/approve/'); ?>' + id,
                    type: 'POST',
                    // dataType: 'json',
                    data: {
                        [csrfName]: csrfToken
                    },
                    success: function(response) {
                        if (response === 'success') {
                            alert('Production batch approved successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + response);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error approving production batch: ' + error);
                    }
                });
            }
        }

        function deleteProduction(id) {
            if (confirm('Are you sure you want to delete this production batch? This action cannot be undone.')) {
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                var csrfName = $('meta[name="csrf-token-name"]').attr('content') || 'csrf_test_name';

                $.ajax({
                    url: '<?= base_url('production/delete/'); ?>' + id,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        [csrfName]: csrfToken
                    },
                    success: function(response) {
                        console.log(response);
                        if (response === 'success') {
                            alert('Production batch deleted successfully!');
                            window.location.href = '<?= base_url('production'); ?>';
                        } else {
                            alert('Error: ' + response);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error deleting production batch: ' + error);
                    }
                });
            }
        }

        // Print functionality
        function printProductionDetails() {
            window.print();
        }
    </script>

    <!-- Make sidebar menu highlighter/selector -->
    <script>
        $(".production-list-active-li").addClass("active");
    </script>
</body>

</html>