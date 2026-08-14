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
        .table-responsive {
            overflow-x: auto;
        }

        body {
            background-color: #f4f6f9;
        }

        .content-wrapper {
            background-color: #f4f6f9;
            padding: 0;
        }

        .content {
            padding: 20px;
        }

        .content-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 20px;
            margin: 0 0 25px 0;
            border-radius: 0 0 8px 8px;
        }

        .content-header h1 {
            color: #1f2937;
            margin: 0 0 10px 0;
            font-size: 28px;
            font-weight: 600;
        }

        .content-header small {
            color: #6b7280;
            font-size: 14px;
        }

        .breadcrumb {
            background-color: transparent;
            padding: 10px 0;
            margin: 10px 0 0 0;
        }

        .breadcrumb a, .breadcrumb li.active {
            color: rgba(255, 255, 255, 0.9);
        }

        .box {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-top: 4px solid #667eea;
            border-radius: 4px;
            margin-bottom: 25px;
            background-color: white;
        }

        .box-header.with-border {
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            padding: 15px;
            margin-bottom: 0;
        }

        .box-body {
            padding: 20px;
        }

        .box-title {
            color: #1f2937;
            font-weight: 600;
            font-size: 16px;
            margin: 0;
        }

        .box-title i {
            color: #667eea;
            margin-right: 8px;
        }

        .form-horizontal .form-group {
            margin-bottom: 20px;
        }

        .form-horizontal .control-label {
            font-weight: 600;
            color: #374151;
            font-size: 14px;
            padding-top: 8px;
        }

        .form-control, .select2-container--default .select2-selection--single {
            border: 1px solid #d1d5db;
            border-radius: 4px;
            height: 38px;
            font-size: 13px;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            border-radius: 4px;
            font-weight: 600;
            font-size: 13px;
            padding: 8px 16px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #667eea;
            border-color: #667eea;
        }

        .btn-primary:hover {
            background-color: #5568d3;
            border-color: #5568d3;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-success {
            background-color: #10b981;
            border-color: #10b981;
        }

        .btn-success:hover {
            background-color: #059669;
            border-color: #059669;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-default {
            background-color: #e5e7eb;
            border-color: #d1d5db;
            color: #374151;
        }

        .btn-default:hover {
            background-color: #d1d5db;
            transform: translateY(-1px);
        }

        .summary-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-stat {
            background: white;
            padding: 20px;
            border-left: 4px solid #667eea;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .summary-stat:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .summary-stat .value {
            font-size: 28px;
            font-weight: 700;
            color: #667eea;
            line-height: 1;
            margin-bottom: 8px;
        }

        .summary-stat .label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .row {
            margin-bottom: 25px;
        }

        .row:last-child {
            margin-bottom: 0;
        }

        .loading-spinner {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
            font-size: 14px;
        }

        .no-data i {
            color: #d1d5db;
            font-size: 48px;
            margin-bottom: 15px;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .button-group .btn {
            flex: 1;
            max-width: 150px;
        }

        .table {
            font-size: 13px;
            margin-bottom: 0;
        }

        .table thead th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: 600;
            border-bottom: 2px solid #e5e7eb;
            padding: 14px 12px;
            vertical-align: middle;
        }

        .table tbody td {
            padding: 14px 12px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        .table tbody strong {
            color: #1f2937;
        }

        .text-right {
            text-align: right;
        }

        .bg-primary {
            background-color: #f3f4f6 !important;
            color: #374151 !important;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(102, 126, 234, 0.02);
        }

        .alert {
            border-radius: 4px;
            border-left: 4px solid #667eea;
        }

        .alert-success {
            border-left-color: #10b981;
            background-color: #f0fdf4;
            color: #065f46;
        }

        .alert-danger {
            border-left-color: #ef4444;
            background-color: #fef2f2;
            color: #7f1d1d;
        }

        .box-body {
            padding: 20px;
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
                    <small>Material input & output products analysis</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="<?= base_url('production'); ?>">Production</a></li>
                    <li class="active"><?= $page_title; ?></li>
                </ol>
            </section>

            <!-- Main Content -->
            <section class="content">

                <!-- FILTER SECTION -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-filter"></i> Filters & Search
                                </h3>
                            </div>

                            <div class="box-body">
                                <form id="filter-form">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 15px; align-items: flex-end;">
                                        <!-- From Date -->
                                        <div>
                                            <label style="display: block; font-weight: 600; color: #374151; font-size: 13px; margin-bottom: 8px;">From Date</label>
                                            <div class="input-group date">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-calendar"></i>
                                                </div>
                                                <input type="text" class="form-control datepicker" id="from_date" name="from_date" value="<?= show_date($from_date); ?>">
                                            </div>
                                        </div>

                                        <!-- To Date -->
                                        <div>
                                            <label style="display: block; font-weight: 600; color: #374151; font-size: 13px; margin-bottom: 8px;">To Date</label>
                                            <div class="input-group date">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-calendar"></i>
                                                </div>
                                                <input type="text" class="form-control datepicker" id="to_date" name="to_date" value="<?= show_date($to_date); ?>">
                                            </div>
                                        </div>

                                        <!-- Recipe -->
                                        <div>
                                            <label style="display: block; font-weight: 600; color: #374151; font-size: 13px; margin-bottom: 8px;">Recipe</label>
                                            <select class="form-control select2" id="recipe_id" style="width: 100%;">
                                                <option value="">All Recipes</option>
                                                <?php foreach ($recipes as $recipe): ?>
                                                    <option value="<?= $recipe->id; ?>"><?= $recipe->recipe_name; ?> (<?= $recipe->output_product_name; ?>)</option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <!-- Material Item -->
                                        <div>
                                            <label style="display: block; font-weight: 600; color: #374151; font-size: 13px; margin-bottom: 8px;">Material Item</label>
                                            <select class="form-control select2" id="item_id" style="width: 100%;">
                                                <option value="">All Materials</option>
                                                <?php foreach ($materials as $material): ?>
                                                    <option value="<?= $material->id; ?>"><?= $material->item_code; ?> - <?= $material->item_name; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Buttons Row -->
                                    <div style="display: flex; gap: 10px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                                        <button type="button" class="btn btn-primary" onclick="applyFilters()">
                                            <i class="fa fa-search"></i> Apply Filters
                                        </button>
                                        <button type="button" class="btn btn-default" onclick="resetFilters()">
                                            <i class="fa fa-refresh"></i> Reset
                                        </button>
                                        <button type="button" class="btn btn-success" onclick="exportReport()">
                                            <i class="fa fa-download"></i> Export CSV
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SUMMARY STATISTICS -->
                <div class="row" id="summary-stats-container">
                    <div class="col-md-12">
                        <div class="summary-row">
                            <div class="summary-stat">
                                <div class="value"><?= $summary_stats->total_batches; ?></div>
                                <div class="label">Total Batches</div>
                            </div>
                            <div class="summary-stat">
                                <div class="value"><?= $summary_stats->total_recipes; ?></div>
                                <div class="label">Total Recipes</div>
                            </div>
                            <div class="summary-stat">
                                <div class="value"><?= $summary_stats->total_unique_materials; ?></div>
                                <div class="label">Unique Materials</div>
                            </div>
                            <div class="summary-stat">
                                <div class="value"><?= number_format($summary_stats->total_output_quantity, 2); ?></div>
                                <div class="label">Total Output</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MATERIAL INPUT SECTION -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-default">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-cube"></i> Material Input (Raw Materials Used)
                                </h3>
                            </div>
                            <div class="box-body">
                                <?php if (empty($material_summary)): ?>
                                    <div class="no-data">
                                        <i class="fa fa-inbox fa-3x" style="color: #ccc;"></i>
                                        <p>No materials data available for the selected period</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="material-table">
                                            <thead class="bg-primary">
                                                <tr>
                                                    <th>Material Code</th>
                                                    <th>Material Name</th>
                                                    <th>Unit</th>
                                                    <th class="text-right">Total Used</th>
                                                    <th class="text-right">Total Batches</th>
                                                    <th style="width: 100px; text-align: center;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($material_summary as $material): ?>
                                                    <tr>
                                                        <td><strong><a href="<?= base_url('items/stock_history/' . $material->item_id); ?>" target="_blank" style="color: #667eea; text-decoration: none;"><?= $material->item_code; ?></a></strong></td>
                                                        <td><?= $material->item_name; ?></td>
                                                        <td><?= $material->unit; ?></td>
                                                        <td class="text-right"><?= number_format($material->total_used, 3); ?></td>
                                                        <td class="text-right"><?= $material->total_batches; ?></td>
                                                        <td style="text-align: center;">
                                                            <button class="btn btn-sm btn-info" onclick="showItemBatchDetails(<?= $material->item_id; ?>, '<?= addslashes($material->item_name); ?>')">
                                                                <i class="fa fa-eye"></i> Details
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- OUTPUT PRODUCTS SECTION -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-default">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-cube"></i> Output Products (Finished Goods Produced)
                                </h3>
                            </div>
                            <div class="box-body">
                                <?php if (empty($output_summary)): ?>
                                    <div class="no-data">
                                        <i class="fa fa-inbox fa-3x" style="color: #ccc;"></i>
                                        <p>No output products data available for the selected period</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="output-table">
                                            <thead class="bg-primary">
                                                <tr>
                                                    <th>Recipe Name</th>
                                                    <th>Output Product</th>
                                                    <th>Unit</th>
                                                    <th class="text-right">Total Produced</th>
                                                    <th class="text-right">Avg per Batch</th>
                                                    <th class="text-right">Total Batches</th>
                                                    <th class="text-right">Materials Used</th>
                                                    <th style="width: 100px; text-align: center;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $total_produced = 0;
                                                $total_batches = 0;
                                                foreach ($output_summary as $output): 
                                                    $total_produced += $output->total_produced;
                                                    $total_batches += $output->total_batches;
                                                ?>
                                                    <tr>
                                                        <td><strong><?= $output->recipe_name; ?></strong></td>
                                                        <td><?= $output->output_product_name; ?></td>
                                                        <td><?= $output->output_unit; ?></td>
                                                        <td class="text-right"><?= number_format($output->total_produced, 2); ?></td>
                                                        <td class="text-right"><?= number_format($output->avg_per_batch, 2); ?></td>
                                                        <td class="text-right"><?= $output->total_batches; ?></td>
                                                        <td class="text-right"><?= $output->unique_items_used; ?></td>
                                                        <td style="text-align: center;">
                                                            <button class="btn btn-sm btn-info" onclick="showRecipeMaterials(<?= $output->recipe_id; ?>, '<?= addslashes($output->recipe_name); ?>')">
                                                                <i class="fa fa-list"></i> Materials
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot style="background-color: #f3f4f6;">
                                                <tr style="font-weight: 600; border-top: 2px solid #667eea;">
                                                    <td colspan="3" style="text-align: right; padding: 12px;">TOTAL:</td>
                                                    <td class="text-right" style="padding: 12px;"><strong><?= number_format($total_produced, 2); ?></strong></td>
                                                    <td></td>
                                                    <td class="text-right" style="padding: 12px;"><strong><?= $total_batches; ?></strong></td>
                                                    <td colspan="2"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </section>

        </div><!-- /.content-wrapper -->
    </div><!-- /.wrapper -->

    <!-- JS -->
    <?php $this->load->view('comman/code_js_datatable'); ?>
    <?php $this->load->view('comman/code_js_form'); ?>

    <script>
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

            // Initialize Select2 for Material Item dropdown
            $('#item_id').select2({
                placeholder: "Select Material",
                allowClear: true,
                width: '100%'
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
         * Apply filters to update report
         */
        function applyFilters() {
            var from_date = $('#from_date').val();
            var to_date = $('#to_date').val();
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

            // Show loading state
            $('#material-table tbody').html('<tr><td colspan="6" style="text-align: center; padding: 20px;"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');
            $('#output-table tbody').html('<tr><td colspan="8" style="text-align: center; padding: 20px;"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');

            // AJAX call to get filtered data (Approved batches only)
            $.ajax({
                type: 'POST',
                url: base_url + 'production_summary/get_report_data',
                data: {
                    from_date: from_date_db,
                    to_date: to_date_db,
                    recipe_id: recipe_id || '',
                    item_id: item_id || ''
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        updateReportDisplay(response);
                        showNotification('Report updated successfully', 'success');
                    } else {
                        showNotification('Error: ' + response.message, 'error');
                    }
                },
                error: function() {
                    showNotification('AJAX Error occurred', 'error');
                }
            });
        }

        /**
         * Update report display with new data
         */
        function updateReportDisplay(response) {
            var materials = response.material_summary;
            var outputs = response.output_summary;
            var stats = response.summary_stats;

            // Update summary statistics
            updateSummaryStats(stats);

            // Update material table
            var materialHtml = '';
            if (materials.length > 0) {
                $.each(materials, function(i, material) {
                    materialHtml += '<tr>';
                    materialHtml += '<td><strong><a href="' + base_url + 'items/stock_history/' + material.item_id + '" target="_blank" style="color: #667eea; text-decoration: none;">' + material.item_code + '</a></strong></td>';
                    materialHtml += '<td>' + material.item_name + '</td>';
                    materialHtml += '<td>' + material.unit + '</td>';
                    materialHtml += '<td class="text-right">' + parseFloat(material.total_used).toFixed(3) + '</td>';
                    materialHtml += '<td class="text-right">' + material.total_batches + '</td>';
                    materialHtml += '<td style="text-align: center;"><button class="btn btn-sm btn-info" onclick="showItemBatchDetails(' + material.item_id + ', \'' + material.item_name.replace(/'/g, "\\'") + '\')"><i class="fa fa-eye"></i> Details</button></td>';
                    materialHtml += '</tr>';
                });
            } else {
                materialHtml = '<tr><td colspan="6" style="text-align: center; padding: 20px; color: #999;">No materials data</td></tr>';
            }
            $('#material-table tbody').html(materialHtml);

            // Update output table with footer totals
            var outputHtml = '';
            var totalProduced = 0;
            var totalBatches = 0;
            
            if (outputs.length > 0) {
                $.each(outputs, function(i, output) {
                    outputHtml += '<tr>';
                    outputHtml += '<td><strong>' + output.recipe_name + '</strong></td>';
                    outputHtml += '<td>' + output.output_product_name + '</td>';
                    outputHtml += '<td>' + output.output_unit + '</td>';
                    outputHtml += '<td class="text-right">' + parseFloat(output.total_produced).toFixed(2) + '</td>';
                    outputHtml += '<td class="text-right">' + parseFloat(output.avg_per_batch).toFixed(2) + '</td>';
                    outputHtml += '<td class="text-right">' + output.total_batches + '</td>';
                    outputHtml += '<td class="text-right">' + output.unique_items_used + '</td>';
                    outputHtml += '<td style="text-align: center;"><button class="btn btn-sm btn-info" onclick="showRecipeMaterials(' + output.recipe_id + ', \'' + output.recipe_name.replace(/'/g, "\\'") + '\')"><i class="fa fa-list"></i> Materials</button></td>';
                    outputHtml += '</tr>';
                    
                    totalProduced += parseFloat(output.total_produced);
                    totalBatches += parseInt(output.total_batches);
                });
                
                $('#output-table tbody').html(outputHtml);
                
                // Update footer with correct totals
                var footerHtml = '<tr style="font-weight: 600; border-top: 2px solid #667eea;">';
                footerHtml += '<td colspan="3" style="text-align: right; padding: 12px;">TOTAL:</td>';
                footerHtml += '<td class="text-right" style="padding: 12px;"><strong>' + totalProduced.toFixed(2) + '</strong></td>';
                footerHtml += '<td></td>';
                footerHtml += '<td class="text-right" style="padding: 12px;"><strong>' + totalBatches + '</strong></td>';
                footerHtml += '<td colspan="2"></td>';
                footerHtml += '</tr>';
                $('#output-table tfoot').html(footerHtml);
            } else {
                outputHtml = '<tr><td colspan="8" style="text-align: center; padding: 20px; color: #999;">No output data</td></tr>';
                $('#output-table tbody').html(outputHtml);
                $('#output-table tfoot').html('');
            }
        }

        /**
         * Update summary statistics section
         */
        function updateSummaryStats(stats) {
            var html = '<div class="summary-row">';
            html += '<div class="summary-stat"><div class="value">' + stats.total_batches + '</div><div class="label">Total Batches</div></div>';
            html += '<div class="summary-stat"><div class="value">' + stats.total_recipes + '</div><div class="label">Total Recipes</div></div>';
            html += '<div class="summary-stat"><div class="value">' + stats.total_unique_materials + '</div><div class="label">Unique Materials</div></div>';
            html += '<div class="summary-stat"><div class="value">' + parseFloat(stats.total_output_quantity).toFixed(2) + '</div><div class="label">Total Output</div></div>';
            html += '</div>';
            $('#summary-stats-container').html(html);
        }

        /**
         * Reset filters
         */
        function resetFilters() {
            $('#filter-form')[0].reset();
            location.reload();
        }

        /**
         * Export report to CSV
         */
        function exportReport() {
            var from_date = $('#from_date').val();
            var to_date = $('#to_date').val();
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

            // Export using GET request (bypasses CSRF)
            window.location.href = base_url + 'production_summary/export_csv?' +
                'from_date=' + encodeURIComponent(from_date_db) +
                '&to_date=' + encodeURIComponent(to_date_db) +
                '&recipe_id=' + encodeURIComponent(recipe_id || '') +
                '&item_id=' + encodeURIComponent(item_id || '');
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

        /**
         * Show batch details for a specific item in modal
         */
        function showItemBatchDetails(itemId, itemName) {
            var from_date = $('#from_date').val();
            var to_date = $('#to_date').val();
            var recipe_id = $('#recipe_id').val();

            // Validate dates
            if (!from_date || !to_date) {
                showNotification('Please select both dates', 'error');
                return;
            }

            // Convert dates to DB format
            var from_date_db = convertToDbFormat(from_date);
            var to_date_db = convertToDbFormat(to_date);

            // Show loading state in modal
            $('#batch-details-body').html('<tr><td colspan="7" style="text-align: center; padding: 40px;"><i class="fa fa-spinner fa-spin"></i> Loading batch details...</td></tr>');
            $('#batch-details-modal-title').text('Batch Details: ' + itemName);
            $('#batchDetailsModal').modal('show');

            // AJAX call to get batch details
            $.ajax({
                type: 'POST',
                url: base_url + 'production_summary/get_item_batch_details',
                data: {
                    item_id: itemId,
                    from_date: from_date_db,
                    to_date: to_date_db,
                    recipe_id: recipe_id || ''
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        updateBatchDetailsDisplay(response.batch_details, itemName);
                    } else {
                        $('#batch-details-body').html('<tr><td colspan="7" style="text-align: center; color: #999;">Error loading batch details</td></tr>');
                    }
                },
                error: function() {
                    $('#batch-details-body').html('<tr><td colspan="7" style="text-align: center; color: #999;">Error occurred while fetching data</td></tr>');
                }
            });
        }

        /**
         * Update batch details modal with data
         */
        function updateBatchDetailsDisplay(batches, itemName) {
            var html = '';
            var totalItemUsed = 0;
            var materialUnit = '';
            
            if (batches.length > 0) {
                $.each(batches, function(i, batch) {
                    if (i === 0) {
                        materialUnit = batch.material_unit || ''; // Get material unit from first batch
                    }
                    html += '<tr>';
                    html += '<td><strong><a href="' + base_url + 'production/view/' + batch.batch_id + '" target="_blank" style="color: #667eea; text-decoration: none;">' + batch.batch_code + '</a></strong></td>';
                    html += '<td>' + batch.recipe_name + '</td>';
                    html += '<td>' + batch.output_product + '</td>';
                    html += '<td>' + batch.output_unit + '</td>';
                    html += '<td class="text-right">' + parseFloat(batch.batch_quantity).toFixed(2) + '</td>';
                    html += '<td class="text-right">' + parseFloat(batch.total_item_used).toFixed(4) + '</td>';
                    html += '<td>' + batch.batch_date + '</td>';
                    html += '</tr>';
                    
                    totalItemUsed += parseFloat(batch.total_item_used);
                });
                
                // Add footer row with total
                html += '<tr style="background-color: #f3f4f6; font-weight: 600; border-top: 2px solid #667eea;">';
                html += '<td colspan="5" style="text-align: right; padding: 12px;">Total Item Used:</td>';
                html += '<td class="text-right" style="padding: 12px;"><strong>' + totalItemUsed.toFixed(4) + '</strong></td>';
                html += '<td style="padding: 12px;"><strong>' + materialUnit + '</strong></td>';
                html += '</tr>';
            } else {
                html = '<tr><td colspan="7" style="text-align: center; padding: 30px; color: #999;">No batch records found for this item in the selected period</td></tr>';
            }
            $('#batch-details-body').html(html);
        }

        /**
         * Show recipe materials in modal
         */
        function showRecipeMaterials(recipeId, recipeName) {
            // Show loading state in modal
            $('#recipe-materials-body').html('<tr><td colspan="5" style="text-align: center; padding: 40px;"><i class="fa fa-spinner fa-spin"></i> Loading materials...</td></tr>');
            $('#recipe-materials-modal-title').text('Recipe Materials: ' + recipeName);
            $('#recipeMaterialsModal').modal('show');

            // AJAX call to get recipe materials
            $.ajax({
                type: 'POST',
                url: base_url + 'production_summary/get_recipe_materials',
                data: {
                    recipe_id: recipeId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        updateRecipeMaterialsDisplay(response.materials);
                    } else {
                        $('#recipe-materials-body').html('<tr><td colspan="5" style="text-align: center; color: #999;">Error loading materials</td></tr>');
                    }
                },
                error: function() {
                    $('#recipe-materials-body').html('<tr><td colspan="5" style="text-align: center; color: #999;">Error occurred while fetching data</td></tr>');
                }
            });
        }

        /**
         * Update recipe materials modal with data
         */
        function updateRecipeMaterialsDisplay(materials) {
            var html = '';
            
            if (materials.length > 0) {
                $.each(materials, function(i, material) {
                    html += '<tr>';
                    html += '<td><strong>' + material.item_code + '</strong></td>';
                    html += '<td>' + material.item_name + '</td>';
                    html += '<td>' + material.unit + '</td>';
                    html += '<td class="text-right">' + parseFloat(material.quantity_per_batch).toFixed(3) + '</td>';
                    html += '<td class="text-right">' + (material.material_cost ? parseFloat(material.material_cost).toFixed(2) : '0.00') + '</td>';
                    html += '</tr>';
                });
            } else {
                html = '<tr><td colspan="5" style="text-align: center; padding: 30px; color: #999;">No materials found for this recipe</td></tr>';
            }
            $('#recipe-materials-body').html(html);
        }

        // Activate sidebar menu item
        $(document).ready(function() {
            $(".production-summary-active-li").addClass("active");
            // Also open parent menu (Production & Recipes)
            $(".recipe-list-active-li.recipe-active-li.production-list-active-li.production-active-li").addClass("active menu-open");
        });
    </script>

    <!-- Batch Details Modal -->
    <div class="modal fade" id="batchDetailsModal" tabindex="-1" role="dialog" aria-labelledby="batch-details-modal-title">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-bottom: none;">
                    <h5 class="modal-title" id="batch-details-modal-title" style="color: white;">Batch Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm">
                            <thead style="background-color: #f3f4f6;">
                                <tr>
                                    <th>Batch #</th>
                                    <th>Recipe</th>
                                    <th>Output Product</th>
                                    <th>Unit</th>
                                    <th class="text-right">Batch Qty</th>
                                    <th class="text-right">Item Used</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody id="batch-details-body">
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 20px;">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer" style="background-color: #f9fafb; border-top: 1px solid #e5e7eb;">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Recipe Materials Modal -->
    <div class="modal fade" id="recipeMaterialsModal" tabindex="-1" role="dialog" aria-labelledby="recipe-materials-modal-title">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border-bottom: none;">
                    <h5 class="modal-title" id="recipe-materials-modal-title" style="color: white;">Recipe Materials</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm">
                            <thead style="background-color: #f3f4f6;">
                                <tr>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th>Unit</th>
                                    <th class="text-right">Qty per Batch</th>
                                    <th class="text-right">Cost</th>
                                </tr>
                            </thead>
                            <tbody id="recipe-materials-body">
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 20px;">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer" style="background-color: #f9fafb; border-top: 1px solid #e5e7eb;">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
