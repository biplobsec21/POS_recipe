<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $page_title; ?> - <?= $this->session->userdata('company_name'); ?></title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- CSS -->
    <?php $this->load->view('comman/code_css_datatable'); ?>
    <?php $this->load->view('comman/code_css_form'); ?>

    <style>
        .info-box {
            margin-bottom: 20px;
        }

        .info-box-number {
            font-size: 28px;
            font-weight: 700;
        }

        .tab-content {
            padding-top: 15px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .status-badge {
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 3px;
            font-weight: bold;
        }

        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }

        .status-draft {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .summary-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }

        .filter-section {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .filter-btn-group {
            margin-top: 15px;
        }

        .loading-spinner {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        .no-data {
            text-align: center;
            padding: 30px;
            color: #999;
            font-size: 14px;
        }

        .item-usage-header {
            background-color: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        <?php $this->load->view('sidebar'); ?>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Content Header -->
            <section class="content-header">
                <h1>
                    <?= $page_title; ?>
                    <small>Production analytics and reports</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="<?= base_url('production'); ?>">Production</a></li>
                    <li class="active"><?= $page_title; ?></li>
                </ol>
            </section>

            <!-- Main Content -->
            <section class="content">

                <!-- QUICK STATS CARDS -->
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-aqua"><i class="fa fa-clock-o"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Today's Productions</span>
                                <span class="info-box-number"><?= $today_count; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-green"><i class="fa fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Approved Today</span>
                                <span class="info-box-number"><?= $today_approved; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-yellow"><i class="fa fa-history"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Batches</span>
                                <span class="info-box-number"><?= $total_count; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-orange"><i class="fa fa-cubes"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Output</span>
                                <span class="info-box-number"><?= number_format($total_output, 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FILTER SECTION -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-primary collapsed">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-filter"></i> Filters & Search
                                </h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="box-body" style="display:none;">
                                <form id="filter-form" class="form-horizontal">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">From Date</label>
                                        <div class="col-sm-2">
                                            <div class="input-group date">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-calendar"></i>
                                                </div>
                                                <input type="text" class="form-control datepicker" id="from_date" name="from_date" value="<?= show_date($from_date); ?>">
                                            </div>
                                        </div>

                                        <label class="col-sm-2 control-label">To Date</label>
                                        <div class="col-sm-2">
                                            <div class="input-group date">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-calendar"></i>
                                                </div>
                                                <input type="text" class="form-control datepicker" id="to_date" name="to_date" value="<?= show_date($to_date); ?>">
                                            </div>
                                        </div>

                                        <label class="col-sm-2 control-label">Status</label>
                                        <div class="col-sm-2">
                                            <select class="form-control" id="status">
                                                <option value="">All Status</option>
                                                <option value="Approved">Approved</option>
                                                <option value="Draft">Draft</option>
                                                <option value="Cancelled">Cancelled</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Recipe</label>
                                        <div class="col-sm-3">
                                            <select class="form-control select2" id="recipe_id" style="width: 100%;">
                                                <option value="">All Recipes</option>
                                                <?php foreach ($recipes as $recipe): ?>
                                                    <option value="<?= $recipe->id; ?>"><?= $recipe->recipe_name; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <label class="col-sm-2 control-label">Item (for usage)</label>
                                        <div class="col-sm-3">
                                            <select class="form-control select2" id="item_id" style="width: 100%;">
                                                <option value="">Select Item</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-sm-12">
                                            <button type="button" class="btn btn-primary" onclick="applyFilters()">
                                                <i class="fa fa-search"></i> Apply Filters
                                            </button>
                                            <button type="button" class="btn btn-default" onclick="resetFilters()">
                                                <i class="fa fa-refresh"></i> Reset
                                            </button>
                                            <button type="button" class="btn btn-success" onclick="exportData()">
                                                <i class="fa fa-download"></i> Export CSV
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABS -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="nav-tabs-custom">
                            <ul class="nav nav-tabs">
                                <li class="active">
                                    <a href="#tab-today" data-toggle="tab">
                                        <i class="fa fa-clock-o"></i> Today's Productions
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab-daterange" data-toggle="tab">
                                        <i class="fa fa-calendar"></i> Date Range
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab-itemusage" data-toggle="tab">
                                        <i class="fa fa-cube"></i> Item Usage Report
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab-summary" data-toggle="tab">
                                        <i class="fa fa-bar-chart"></i> Summary & Totals
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content">

                                <!-- TAB 1: TODAY'S PRODUCTIONS -->
                                <div class="tab-pane active" id="tab-today">
                                    <div class="box box-default">
                                        <div class="box-header with-border">
                                            <h3 class="box-title">
                                                Today's Production Batches (<?= count($today_productions); ?> batches)
                                            </h3>
                                        </div>
                                        <div class="box-body">
                                            <?php if (empty($today_productions)): ?>
                                                <div class="no-data">
                                                    <i class="fa fa-inbox fa-3x" style="color: #ccc;"></i>
                                                    <p>No productions created today</p>
                                                </div>
                                            <?php else: ?>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped">
                                                        <thead class="bg-primary">
                                                            <tr>
                                                                <th>Batch Code</th>
                                                                <th>Recipe</th>
                                                                <th>Output Product</th>
                                                                <th>Batch Qty</th>
                                                                <th>Total Output</th>
                                                                <th>Cost</th>
                                                                <th>Status</th>
                                                                <th>Created By</th>
                                                                <th>Time</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($today_productions as $prod): ?>
                                                                <tr>
                                                                    <td><strong><?= $prod->batch_code; ?></strong></td>
                                                                    <td><?= $prod->recipe_name; ?></td>
                                                                    <td><?= $prod->output_product_name; ?></td>
                                                                    <td class="text-right"><?= number_format($prod->batch_quantity, 2); ?></td>
                                                                    <td class="text-right"><?= number_format($prod->total_output_qty, 2); ?></td>
                                                                    <td class="text-right"><?= number_format($prod->total_cost, 2); ?> Tk</td>
                                                                    <td>
                                                                        <span class="status-badge status-<?= strtolower($prod->status); ?>">
                                                                            <?= $prod->status; ?>
                                                                        </span>
                                                                    </td>
                                                                    <td><?= $prod->created_by_name; ?></td>
                                                                    <td><?= substr($prod->created_at, 11, 5); ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 2: DATE RANGE PRODUCTIONS -->
                                <div class="tab-pane" id="tab-daterange">
                                    <div class="box box-default">
                                        <div class="box-header with-border">
                                            <h3 class="box-title">
                                                Productions from <?= $from_date; ?> to <?= $to_date; ?>
                                            </h3>
                                        </div>
                                        <div class="box-body">
                                            <!-- Summary cards for date range -->
                                            <div class="row" style="margin-bottom: 15px;">
                                                <div class="col-md-2">
                                                    <div class="small-box bg-aqua">
                                                        <div class="inner">
                                                            <h3><?= $date_range_summary->total_batches; ?></h3>
                                                            <p>Total Batches</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="small-box bg-green">
                                                        <div class="inner">
                                                            <h3><?= $date_range_summary->approved_batches; ?></h3>
                                                            <p>Approved</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="small-box bg-yellow">
                                                        <div class="inner">
                                                            <h3><?= $date_range_summary->draft_batches; ?></h3>
                                                            <p>Draft</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="small-box bg-red">
                                                        <div class="inner">
                                                            <h3><?= $date_range_summary->cancelled_batches; ?></h3>
                                                            <p>Cancelled</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="small-box bg-purple">
                                                        <div class="inner">
                                                            <h3><?= number_format($date_range_summary->total_output, 0); ?></h3>
                                                            <p>Total Output</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="small-box bg-orange">
                                                        <div class="inner">
                                                            <h3><?= number_format($date_range_summary->total_cost, 0); ?></h3>
                                                            <p>Total Cost (Tk)</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Table -->
                                            <?php if (empty($date_range_productions)): ?>
                                                <div class="no-data">
                                                    <i class="fa fa-inbox fa-3x" style="color: #ccc;"></i>
                                                    <p>No productions found in this date range</p>
                                                </div>
                                            <?php else: ?>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped">
                                                        <thead class="bg-primary">
                                                            <tr>
                                                                <th>Batch Code</th>
                                                                <th>Recipe</th>
                                                                <th>Output Product</th>
                                                                <th>Batch Qty</th>
                                                                <th>Total Output</th>
                                                                <th>Cost</th>
                                                                <th>Cost/Unit</th>
                                                                <th>Status</th>
                                                                <th>Created Date</th>
                                                                <th>Created By</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($date_range_productions as $prod): ?>
                                                                <tr>
                                                                    <td><strong><?= $prod->batch_code; ?></strong></td>
                                                                    <td><?= $prod->recipe_name; ?></td>
                                                                    <td><?= $prod->output_product_name; ?></td>
                                                                    <td class="text-right"><?= number_format($prod->batch_quantity, 2); ?></td>
                                                                    <td class="text-right"><?= number_format($prod->total_output_qty, 2); ?></td>
                                                                    <td class="text-right"><?= number_format($prod->total_cost, 2); ?> Tk</td>
                                                                    <td class="text-right"><?= number_format($prod->cost_per_unit, 2); ?> Tk</td>
                                                                    <td>
                                                                        <span class="status-badge status-<?= strtolower($prod->status); ?>">
                                                                            <?= $prod->status; ?>
                                                                        </span>
                                                                    </td>
                                                                    <td><?= substr($prod->created_at, 0, 10); ?></td>
                                                                    <td><?= $prod->created_by_name; ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 3: ITEM USAGE REPORT -->
                                <div class="tab-pane" id="tab-itemusage">
                                    <div class="box box-default">
                                        <div class="box-header with-border">
                                            <h3 class="box-title">Item Usage in Productions</h3>
                                        </div>
                                        <div class="box-body">
                                            <div class="alert alert-info">
                                                <i class="fa fa-info-circle"></i> 
                                                Select an item from the filters above and click <strong>Apply Filters</strong> to view how many units were used in productions within the date range.
                                            </div>

                                            <div id="item-usage-container">
                                                <div class="no-data">
                                                    <i class="fa fa-inbox fa-3x" style="color: #ccc;"></i>
                                                    <p>Select an item and apply filters to view usage report</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 4: SUMMARY & TOTALS -->
                                <div class="tab-pane" id="tab-summary">
                                    <div class="box box-default">
                                        <div class="box-header with-border">
                                            <h3 class="box-title">Lifetime Production Summary</h3>
                                        </div>
                                        <div class="box-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <table class="table table-bordered">
                                                        <tbody>
                                                            <tr>
                                                                <td><strong>Total Batches:</strong></td>
                                                                <td class="text-right"><?= $total_summary->total_batches; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Approved:</strong></td>
                                                                <td class="text-right"><?= $total_summary->approved_batches; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Draft:</strong></td>
                                                                <td class="text-right"><?= $total_summary->draft_batches; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Cancelled:</strong></td>
                                                                <td class="text-right"><?= $total_summary->cancelled_batches; ?></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <div class="col-md-6">
                                                    <table class="table table-bordered">
                                                        <tbody>
                                                            <tr>
                                                                <td><strong>Total Output:</strong></td>
                                                                <td class="text-right"><?= number_format($total_summary->total_output, 2); ?> units</td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Total Cost:</strong></td>
                                                                <td class="text-right"><?= number_format($total_summary->total_cost, 2); ?> Tk</td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Avg Cost/Unit:</strong></td>
                                                                <td class="text-right"><?= number_format($total_summary->avg_cost_per_unit, 2); ?> Tk</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </section>
        </div>

        <?php $this->load->view('footer'); ?>
        <div class="control-sidebar-bg"></div>
    </div>

    <!-- JavaScript -->
    <?php $this->load->view('comman/code_js_datatable'); ?>
    <?php $this->load->view('comman/code_js_form'); ?>

    <script type="text/javascript">
        var base_url = '<?= base_url(); ?>';

        $(document).ready(function() {
            // Initialize datepickers with system format (DD-MM-YYYY)
            $('.datepicker').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true,
                todayHighlight: true,
                orientation: "bottom auto"
            });

            // Initialize Select2 for Recipe dropdown
            $('#recipe_id').select2({
                placeholder: "Select Recipe",
                allowClear: true,
                width: '100%'
            });

            // Initialize Select2 for Item dropdown with AJAX search
            $('#item_id').select2({
                placeholder: "Search and select item",
                allowClear: true,
                width: '100%',
                ajax: {
                    url: base_url + 'production_dashboard/get_items_json',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term // search term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0, // Allow showing results without typing
                templateResult: function(data) {
                    if (!data.id) { return data.text; }
                    return data.text;
                },
                templateSelection: function(data) {
                    if (!data.id) { return data.text; }
                    return data.text;
                }
            });

            // Load items on page load or when item dropdown is opened
            $('#item_id').on('select2:opening', function(e) {
                if ($('#item_id').data('select2').$dropdown.find('.select2-results__option').length === 0) {
                    // Trigger AJAX to load initial items
                    $.ajax({
                        url: base_url + 'production_dashboard/get_items_json',
                        dataType: 'json',
                        data: { search: '' },
                        success: function(data) {
                            $('#item_id').select2('destroy').select2({
                                placeholder: "Search and select item",
                                allowClear: true,
                                width: '100%',
                                ajax: {
                                    url: base_url + 'production_dashboard/get_items_json',
                                    dataType: 'json',
                                    delay: 250,
                                    data: function(params) {
                                        return { search: params.term };
                                    },
                                    processResults: function(data) {
                                        return { results: data.results };
                                    },
                                    cache: true
                                },
                                minimumInputLength: 0,
                                templateResult: function(data) {
                                    if (!data.id) { return data.text; }
                                    return data.text;
                                },
                                templateSelection: function(data) {
                                    if (!data.id) { return data.text; }
                                    return data.text;
                                }
                            });
                        }
                    });
                }
            });

            // Tab change event for item usage
            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                var target = $(e.target).attr("href");
                if (target === '#tab-itemusage') {
                    // Check if item is selected
                    if ($('#item_id').val()) {
                        loadItemUsage();
                    }
                }
            });
        });

        /**
         * Convert date from DD-MM-YYYY to YYYY-MM-DD format for database
         */
        function convertToDbFormat(dateString) {
            if (!dateString) return '';
            var parts = dateString.split('-');
            if (parts.length === 3) {
                return parts[2] + '-' + parts[1] + '-' + parts[0]; // YYYY-MM-DD
            }
            return dateString;
        }

        /**
         * Validate date format (DD-MM-YYYY)
         */
        function isValidDateFormat(dateString) {
            var pattern = /^(\d{2})-(\d{2})-(\d{4})$/;
            if (!pattern.test(dateString)) {
                return false;
            }

            var parts = dateString.split('-');
            var day = parseInt(parts[0], 10);
            var month = parseInt(parts[1], 10);
            var year = parseInt(parts[2], 10);

            if (year < 1000 || year > 3000 || month == 0 || month > 12) {
                return false;
            }

            var monthLength = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

            if (year % 400 == 0 || (year % 100 != 0 && year % 4 == 0)) {
                monthLength[1] = 29;
            }

            return day > 0 && day <= monthLength[month - 1];
        }

        /**
         * Validate date range
         */
        function isValidDateRange(fromDate, toDate) {
            var fromParts = fromDate.split('-');
            var toParts = toDate.split('-');
            
            var fromDateObj = new Date(fromParts[2], fromParts[1] - 1, fromParts[0]);
            var toDateObj = new Date(toParts[2], toParts[1] - 1, toParts[0]);
            
            return fromDateObj <= toDateObj;
        }

        /**
         * Apply filters to update date range and item usage
         */
        function applyFilters() {
            var from_date = $('#from_date').val();
            var to_date = $('#to_date').val();
            var status = $('#status').val();
            var recipe_id = $('#recipe_id').val();
            var item_id = $('#item_id').val();

            // Validate dates
            if (!from_date || !to_date) {
                showNotification('Please select both dates', 'error');
                return;
            }

            if (!isValidDateFormat(from_date) || !isValidDateFormat(to_date)) {
                showNotification('Please select valid dates in DD-MM-YYYY format', 'error');
                return;
            }

            if (!isValidDateRange(from_date, to_date)) {
                showNotification('From date cannot be after To date', 'error');
                return;
            }

            // Convert dates to DB format (YYYY-MM-DD)
            var from_date_db = convertToDbFormat(from_date);
            var to_date_db = convertToDbFormat(to_date);

            // Update date range tab
            updateDateRangeTab(from_date_db, to_date_db, status, recipe_id);

            // Update item usage if item is selected
            if (item_id) {
                updateItemUsageTab(item_id, from_date_db, to_date_db, status);
            }
        }

        /**
         * Update date range productions
         */
        function updateDateRangeTab(from_date, to_date, status, recipe_id) {
            // Show loading state
            var loadingHtml = '<div class="loading-spinner"><i class="fa fa-spinner fa-spin fa-2x"></i> Loading...</div>';
            
            $.ajax({
                type: 'POST',
                url: base_url + 'production_dashboard/get_date_range_productions',
                data: {
                    from_date: from_date,
                    to_date: to_date,
                    status: status,
                    recipe_id: recipe_id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var productions = response.productions;
                        var summary = response.summary;
                        
                        // ✅ Update the header title with filtered dates
                        $('#tab-daterange .box-header h3').text('Productions from ' + from_date + ' to ' + to_date);
                        
                        // Build summary cards HTML
                        var summaryHtml = '<div class="row" style="margin-bottom: 15px;">';
                        summaryHtml += '<div class="col-md-2"><div class="small-box bg-aqua"><div class="inner"><h3>' + summary.total_batches + '</h3><p>Total Batches</p></div></div></div>';
                        summaryHtml += '<div class="col-md-2"><div class="small-box bg-green"><div class="inner"><h3>' + summary.approved_batches + '</h3><p>Approved</p></div></div></div>';
                        summaryHtml += '<div class="col-md-2"><div class="small-box bg-yellow"><div class="inner"><h3>' + summary.draft_batches + '</h3><p>Draft</p></div></div></div>';
                        summaryHtml += '<div class="col-md-2"><div class="small-box bg-red"><div class="inner"><h3>' + summary.cancelled_batches + '</h3><p>Cancelled</p></div></div></div>';
                        summaryHtml += '<div class="col-md-2"><div class="small-box bg-purple"><div class="inner"><h3>' + Math.round(summary.total_output) + '</h3><p>Total Output</p></div></div></div>';
                        summaryHtml += '<div class="col-md-2"><div class="small-box bg-orange"><div class="inner"><h3>' + Math.round(summary.total_cost) + '</h3><p>Total Cost (Tk)</p></div></div></div>';
                        summaryHtml += '</div>';
                        
                        // Build table HTML
                        var tableHtml = '';
                        if (productions.length > 0) {
                            tableHtml += '<div class="table-responsive"><table class="table table-bordered table-striped">';
                            tableHtml += '<thead class="bg-primary"><tr>';
                            tableHtml += '<th>Batch Code</th><th>Recipe</th><th>Output Product</th>';
                            tableHtml += '<th>Batch Qty</th><th>Total Output</th><th>Cost</th>';
                            tableHtml += '<th>Cost/Unit</th><th>Status</th><th>Created Date</th><th>Created By</th>';
                            tableHtml += '</tr></thead><tbody>';
                            
                            $.each(productions, function(i, prod) {
                                tableHtml += '<tr>';
                                tableHtml += '<td><strong>' + prod.batch_code + '</strong></td>';
                                tableHtml += '<td>' + prod.recipe_name + '</td>';
                                tableHtml += '<td>' + prod.output_product_name + '</td>';
                                tableHtml += '<td class="text-right">' + parseFloat(prod.batch_quantity).toFixed(2) + '</td>';
                                tableHtml += '<td class="text-right">' + parseFloat(prod.total_output_qty).toFixed(2) + '</td>';
                                tableHtml += '<td class="text-right">' + parseFloat(prod.total_cost).toFixed(2) + ' Tk</td>';
                                tableHtml += '<td class="text-right">' + parseFloat(prod.cost_per_unit).toFixed(2) + ' Tk</td>';
                                tableHtml += '<td><span class="status-badge status-' + prod.status.toLowerCase() + '">' + prod.status + '</span></td>';
                                tableHtml += '<td>' + prod.created_at.substring(0, 10) + '</td>';
                                tableHtml += '<td>' + prod.created_by_name + '</td>';
                                tableHtml += '</tr>';
                            });
                            
                            tableHtml += '</tbody></table></div>';
                        } else {
                            tableHtml += '<div class="no-data"><i class="fa fa-inbox fa-3x" style="color: #ccc;"></i><p>No productions found</p></div>';
                        }
                        
                        // Update the tab content
                        $('#tab-daterange .box-body').html(summaryHtml + tableHtml);
                        
                        showNotification('Filters applied successfully', 'success');
                        $('a[href="#tab-daterange"]').tab('show');
                    } else {
                        showNotification('Error: ' + response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    showNotification('AJAX Error: ' + error, 'error');
                    console.error('AJAX Error:', xhr.responseText);
                }
            });
        }

        /**
         * Update item usage report
         */
        function updateItemUsageTab(item_id, from_date, to_date, status) {
            var container = $('#item-usage-container');
            container.html('<div class="loading-spinner"><i class="fa fa-spinner fa-spin fa-2x"></i> Loading...</div>');

            $.ajax({
                type: 'POST',
                url: base_url + 'production_dashboard/get_item_usage',
                data: {
                    item_id: item_id,
                    from_date: from_date,
                    to_date: to_date,
                    status: status || ''
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var summary = response.summary;
                        var report = response.report;

                        var html = '<div class="item-usage-header">';
                        html += '<strong>' + summary.item_code + ' - ' + summary.item_name + '</strong><br>';
                        html += 'Total Consumed: <strong>' + parseFloat(summary.total_consumed).toFixed(2) + '</strong> units | ';
                        html += 'Used in <strong>' + summary.production_count + '</strong> productions';
                        if (status) {
                            html += ' (Status: ' + status + ')';
                        }
                        html += '</div>';

                        if (report.length > 0) {
                            html += '<div class="table-responsive"><table class="table table-bordered table-striped">';
                            html += '<thead class="bg-primary"><tr>';
                            html += '<th>Batch Code</th><th>Recipe</th><th>Batch Qty</th>';
                            html += '<th>Qty Per Batch</th><th>Total Consumed</th>';
                            html += '<th>Status</th><th>Created Date</th><th>Created By</th>';
                            html += '</tr></thead><tbody>';

                            $.each(report, function(i, item) {
                                html += '<tr>';
                                html += '<td><strong>' + item.batch_code + '</strong></td>';
                                html += '<td>' + item.recipe_name + '</td>';
                                html += '<td class="text-right">' + parseFloat(item.batch_quantity).toFixed(2) + '</td>';
                                html += '<td class="text-right">' + parseFloat(item.quantity_per_batch).toFixed(2) + '</td>';
                                html += '<td class="text-right"><strong>' + parseFloat(item.total_consumed).toFixed(2) + '</strong></td>';
                                html += '<td><span class="status-badge status-' + item.status.toLowerCase() + '">' + item.status + '</span></td>';
                                html += '<td>' + item.created_at.substring(0, 10) + '</td>';
                                html += '<td>' + item.created_by_name + '</td>';
                                html += '</tr>';
                            });

                            html += '</tbody></table></div>';
                        } else {
                            html += '<div class="no-data">';
                            html += '<i class="fa fa-inbox fa-3x" style="color: #ccc;"></i>';
                            html += '<p>No usage found for this item in the selected date range</p>';
                            html += '</div>';
                        }

                        container.html(html);
                    } else {
                        showNotification('Error: ' + response.message, 'error');
                        container.html('<div class="no-data">Error loading data</div>');
                    }
                },
                error: function() {
                    showNotification('AJAX Error occurred', 'error');
                    container.html('<div class="no-data">Error loading data</div>');
                }
            });
        }

        /**
         * Load item usage (called when tab is clicked)
         */
        function loadItemUsage() {
            var item_id = $('#item_id').val();
            var from_date = $('#from_date').val();
            var to_date = $('#to_date').val();
            var status = $('#status').val();

            if (item_id && from_date && to_date) {
                if (isValidDateFormat(from_date) && isValidDateFormat(to_date)) {
                    var from_date_db = convertToDbFormat(from_date);
                    var to_date_db = convertToDbFormat(to_date);
                    updateItemUsageTab(item_id, from_date_db, to_date_db, status);
                }
            }
        }

        /**
         * Reset filters
         */
        function resetFilters() {
            $('#filter-form')[0].reset();
            location.reload();
        }

        /**
         * Export data to CSV
         */
        function exportData() {
            var from_date = $('#from_date').val();
            var to_date = $('#to_date').val();
            var status = $('#status').val();
            var recipe_id = $('#recipe_id').val();
            var item_id = $('#item_id').val();

            if (!from_date || !to_date) {
                showNotification('Please select both dates', 'error');
                return;
            }

            if (!isValidDateFormat(from_date) || !isValidDateFormat(to_date)) {
                showNotification('Please select valid dates in DD-MM-YYYY format', 'error');
                return;
            }

            var from_date_db = convertToDbFormat(from_date);
            var to_date_db = convertToDbFormat(to_date);

            if (item_id) {
                var form = $('<form>', {
                    'method': 'POST',
                    'action': base_url + 'production_dashboard/export_item_usage'
                }).append($('<input>', {
                    'type': 'hidden',
                    'name': 'item_id',
                    'value': item_id
                })).append($('<input>', {
                    'type': 'hidden',
                    'name': 'from_date',
                    'value': from_date_db
                })).append($('<input>', {
                    'type': 'hidden',
                    'name': 'to_date',
                    'value': to_date_db
                })).append($('<input>', {
                    'type': 'hidden',
                    'name': 'status',
                    'value': status || ''
                }));

                $('body').append(form);
                form.submit();
                form.remove();
            } else {
                var form = $('<form>', {
                    'method': 'POST',
                    'action': base_url + 'production_dashboard/export_productions'
                }).append($('<input>', {
                    'type': 'hidden',
                    'name': 'from_date',
                    'value': from_date_db
                })).append($('<input>', {
                    'type': 'hidden',
                    'name': 'to_date',
                    'value': to_date_db
                })).append($('<input>', {
                    'type': 'hidden',
                    'name': 'status',
                    'value': status
                })).append($('<input>', {
                    'type': 'hidden',
                    'name': 'recipe_id',
                    'value': recipe_id
                }));

                $('body').append(form);
                form.submit();
                form.remove();
            }
        }

        /**
         * Show notification
         */
        function showNotification(message, type) {
            var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            var html = '<div class="alert ' + alertClass + ' alert-dismissible">';
            html += '<button type="button" class="close" data-dismiss="alert">&times;</button>';
            html += message;
            html += '</div>';

            $('section.content').prepend(html);

            setTimeout(function() {
                $('.alert').fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }
    </script>

</body>

</html>
