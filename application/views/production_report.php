<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $page_title; ?> - <?= $this->session->userdata('company_name'); ?></title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- TABLES CSS CODE -->
    <?php include "comman/code_css_form.php"; ?>

    <style>
        .bg-blue {
            background-color: #3c8dbc !important;
            color: white;
        }

        .text-bold {
            font-weight: bold;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .loading-spinner {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        .total-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }

        .status-badge {
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 3px;
        }

        .export-buttons {
            margin-bottom: 10px;
        }
    </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        <?php include "sidebar.php"; ?>
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>
                    <?= $page_title; ?>
                    <small>Production batches and output reports</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="<?php echo $base_url; ?>reports">Reports</a></li>
                    <li class="active"><?= $page_title; ?></li>
                </ol>
            </section>
            <!-- Main content -->
            <section class="content">
                <div class="row">
                    <!-- right column -->
                    <div class="col-md-12">
                        <!-- Horizontal Form -->
                        <div class="box box-info ">
                            <div class="box-header with-border">
                                <h3 class="box-title">Production Report Filters</h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- /.box-header -->
                            <!-- form start -->
                            <form class="form-horizontal" id="report-form" onkeypress="return event.keyCode != 13;">
                                <input type="hidden" id="base_url" value="<?php echo $base_url; ?>">
                                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="from_date" class="col-sm-2 control-label">From Date <span class="text-red">*</span></label>
                                        <div class="col-sm-3">
                                            <div class="input-group date">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-calendar"></i>
                                                </div>
                                                <input type="text" class="form-control pull-right datepicker" id="from_date" name="from_date" value="<?php echo show_date(date('d-m-Y', strtotime('-30 days'))); ?>">
                                            </div>
                                            <span id="from_date_msg" style="display:none" class="text-danger"></span>
                                        </div>
                                        <label for="to_date" class="col-sm-2 control-label">To Date <span class="text-red">*</span></label>
                                        <div class="col-sm-3">
                                            <div class="input-group date">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-calendar"></i>
                                                </div>
                                                <input type="text" class="form-control pull-right datepicker" id="to_date" name="to_date" value="<?php echo show_date(date('d-m-Y')); ?>">
                                            </div>
                                            <span id="to_date_msg" style="display:none" class="text-danger"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="status" class="col-sm-2 control-label">Status</label>
                                        <div class="col-sm-3">
                                            <select class="form-control select2" id="status" name="status" style="width: 100%;">
                                                <option value="">- All Status -</option>
                                                <option value="Draft">Draft</option>
                                                <option value="Approved">Approved</option>
                                                <option value="Cancelled">Cancelled</option>
                                            </select>
                                            <span id="status_msg" style="display:none" class="text-danger"></span>
                                        </div>
                                        <div class="col-sm-2">
                                            <button type="button" id="reset" class="btn btn-default" title="Reset Filters">
                                                <i class="fa fa-refresh"></i> Reset
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.box-body -->
                                <div class="box-footer">
                                    <div class="col-sm-8 col-sm-offset-2 text-center">
                                        <div class="col-md-3 col-md-offset-3">
                                            <button type="button" id="view" class="btn btn-block btn-success" title="Show Data">
                                                <i class="fa fa-search"></i> Show Report
                                            </button>
                                        </div>
                                        <div class="col-sm-3">
                                            <a href="<?= base_url('dashboard'); ?>">
                                                <button type="button" class="btn btn-block btn-warning close_btn" title="Go Dashboard">
                                                    <i class="fa fa-times"></i> Close
                                                </button>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.box-footer -->
                            </form>
                        </div>
                        <!-- /.box -->
                    </div>
                    <!--/.col (right) -->
                </div>
                <!-- /.row -->
            </section>
            <!-- /.content -->

            <!-- Report Results Section -->
            <section class="content">
                <div class="row">
                    <!-- right column -->
                    <div class="col-md-12">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Production Batches Report</h3>
                                <div class="box-tools pull-right">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-success btn-sm" onclick="exportReport('excel')">
                                            <i class="fa fa-file-excel-o"></i> Excel
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="exportReport('pdf')">
                                            <i class="fa fa-file-pdf-o"></i> PDF
                                        </button>
                                        <button type="button" class="btn btn-default btn-sm" onclick="exportReport('print')">
                                            <i class="fa fa-print"></i> Print
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-striped" id="report-data">
                                        <thead>
                                            <tr class="bg-blue">
                                                <th width="3%">#</th>
                                                <th width="12%">Batch Code</th>
                                                <th width="15%">Recipe Name</th>
                                                <th width="10%">Batch Quantity</th>
                                                <th width="10%">Output Quantity</th>
                                                <th width="10%">Total Cost (<?= $CI->currency(); ?>)</th>
                                                <th width="10%">Cost Per Unit (<?= $CI->currency(); ?>)</th>
                                                <th width="10%">Production Date</th>
                                                <th width="8%">Status</th>
                                                <th width="12%">Created By</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyid">
                                            <tr>
                                                <td colspan="10" class="text-center text-muted">
                                                    <i class="fa fa-info-circle"></i> Please select filters and click "Show Report" to view production data
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot id="tablefoot" style="display: none;">
                                            <tr class="total-row">
                                                <td colspan="3" class="text-right text-bold">Total:</td>
                                                <td class="text-right text-bold" id="total-batch-qty">0.00</td>
                                                <td class="text-right text-bold" id="total-output-qty">0.00</td>
                                                <td class="text-right text-bold" id="total-cost">0.00</td>
                                                <td class="text-right">-</td>
                                                <td colspan="3"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <!-- /.box-body -->
                            <div class="box-footer">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="legend-container">
                                            <div class="legend-item">
                                                <span class="label label-success status-badge">Approved</span>
                                                <small>Completed and approved batches</small>
                                            </div>
                                            <div class="legend-item">
                                                <span class="label label-warning status-badge">Draft</span>
                                                <small>Pending approval batches</small>
                                            </div>
                                            <div class="legend-item">
                                                <span class="label label-danger status-badge">Cancelled</span>
                                                <small>Cancelled batches</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <small class="text-muted" id="report-info">
                                            Report generated on: <?php echo date('d-m-Y H:i:s'); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.box -->
                    </div>
                </div>
            </section>
        </div>
        <!-- /.content-wrapper -->
        <?php include "footer.php"; ?>
        <div class="control-sidebar-bg"></div>
    </div>
    <!-- ./wrapper -->

    <!-- SOUND CODE -->
    <?php include "comman/code_js_sound.php"; ?>
    <!-- TABLES CODE -->
    <?php include "comman/code_js_form.php"; ?>
    <!-- TABLE EXPORT CODE -->
    <?php include "comman/code_js_export.php"; ?>

    <!-- CSRF and Base URL Configuration -->
    <script>
        // Global variables for CSRF protection and base URL
        var base_url = "<?php echo $base_url; ?>";
        var csrf_token_name = "<?php echo $this->security->get_csrf_token_name(); ?>";
        var csrf_hash = "<?php echo $this->security->get_csrf_hash(); ?>";
        var currency_symbol = "<?= $CI->currency(); ?>";

        console.log("Production Report Configuration:", {
            base_url: base_url,
            csrf_token_name: csrf_token_name,
            currency_symbol: currency_symbol
        });
    </script>

    <!-- Production Report JavaScript -->
    <script src="<?php echo $theme_link; ?>js/report-production.js"></script>

    <!-- Make sidebar menu highlighter/selector -->
    <script>
        $(document).ready(function() {
            // Set active menu item
            $(".<?php echo basename(__FILE__, '.php'); ?>-active-li").addClass("active");

            // Initialize tooltips
            $('[title]').tooltip();

            // Reset button functionality
            $('#reset').click(function() {
                $('#from_date').val('<?php echo show_date(date('d-m-Y', strtotime('-30 days'))); ?>');
                $('#to_date').val('<?php echo show_date(date('d-m-Y')); ?>');
                $('#status').val('').trigger('change');
                $('#tbodyid').html('<tr><td colspan="10" class="text-center text-muted"><i class="fa fa-info-circle"></i> Please select filters and click "Show Report" to view production data</td></tr>');
                $('#tablefoot').hide();
                toastr.info('Filters have been reset');
            });

            // Auto-adjust table header widths
            function adjustTableHeader() {
                var $table = $('#report-data');
                var $header = $table.find('thead tr');
                var $firstRow = $table.find('tbody tr:first');

                if ($firstRow.length > 0 && !$firstRow.find('td').hasClass('text-center')) {
                    $header.find('th').each(function(i) {
                        var $headerCell = $(this);
                        var $firstCell = $firstRow.find('td').eq(i);
                        if ($firstCell.length > 0) {
                            $headerCell.width($firstCell.width());
                        }
                    });
                }
            }

            // Adjust table on window resize
            $(window).resize(function() {
                adjustTableHeader();
            });
        });
    </script>

    <!-- Additional Styles -->
    <style>
        .legend-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .legend-item small {
            color: #666;
        }

        .box-primary {
            border-top-color: #3c8dbc;
        }

        .table-striped>tbody>tr:nth-of-type(odd) {
            background-color: #f9f9f9;
        }

        .table-hover>tbody>tr:hover {
            background-color: #f5f5f5;
        }

        .bg-blue th {
            border-bottom: 2px solid #2d6a9f;
        }

        .total-row td {
            border-top: 2px solid #ddd;
            background-color: #e8f4fd !important;
        }

        .loading-spinner i {
            margin-right: 8px;
        }

        @media (max-width: 768px) {
            .legend-container {
                flex-direction: column;
                align-items: flex-start;
            }

            .box-tools .btn-group {
                margin-top: 10px;
            }
        }
    </style>
</body>

</html>