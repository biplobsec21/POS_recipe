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
            <section class="content-header" style="padding-bottom: 30px;">
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
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-filter"></i> Filters & Search
                                </h3>
                            </div>

                            <div class="box-body">
                                <form id="filter-form" class="form-horizontal">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; align-items: flex-end;">
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

                                        <!-- Buttons -->
                                        <div style="display: flex; gap: 10px;">
                                            <button type="button" class="btn btn-primary" onclick="applyFilters()">
                                                <i class="fa fa-search"></i> Apply
                                            </button>
                                            <button type="button" class="btn btn-default" onclick="resetFilters()">
                                                <i class="fa fa-refresh"></i> Reset
                                            </button>
                                            <button type="button" class="btn btn-success" onclick="exportCSV()">
                                                <i class="fa fa-download"></i> Export CSV
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PRODUCTIONS TABLE -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-default">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-list"></i> Production List
                                </h3>
                            </div>
                            <div class="box-body" id="productions-container">
                                <?php if (empty($date_range_productions)): ?>
                                    <div class="no-data">
                                        <i class="fa fa-inbox fa-3x" style="color: #ccc;"></i>
                                        <p>No productions found</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead class="bg-primary">
                                                <tr>
                                                    <th>Batch Code</th>
                                                    <th>Recipe</th>
                                                    <th>Output Product</th>
                                                    <th class="text-right">Batch Qty</th>
                                                    <th class="text-right">Total Output</th>
                                                    <th class="text-right">Cost</th>
                                                    <th class="text-right">Cost/Unit</th>
                                                    <th>Status</th>
                                                    <th>Approved Date</th>
                                                    <th>Created Date</th>
                                                    <th>Created By</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($date_range_productions as $prod): ?>
                                                    <tr>
                                                        <td><strong><a href="<?= base_url('production/view/' . $prod->id); ?>" target="_blank" style="color: #667eea; text-decoration: none;"><?= $prod->batch_code; ?></a></strong></td>
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
                                                        <td><?= substr($prod->approved_at, 0, 10); ?></td>
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
            updateDateRangeTab(from_date_db, to_date_db);
        }

        /**
         * Update date range productions
         */
        function updateDateRangeTab(from_date, to_date) {
            // Show loading state
            var loadingHtml = '<div class="loading-spinner"><i class="fa fa-spinner fa-spin fa-2x"></i> Loading...</div>';
            
            $.ajax({
                type: 'POST',
                url: base_url + 'production_dashboard/get_date_range_productions',
                data: {
                    from_date: from_date,
                    to_date: to_date
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var productions = response.productions;
                        var summary = response.summary;
                        
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
                            tableHtml += '<th class="text-right">Batch Qty</th><th class="text-right">Total Output</th><th class="text-right">Cost</th>';
                            tableHtml += '<th class="text-right">Cost/Unit</th><th>Status</th><th>Approved Date</th><th>Created Date</th><th>Created By</th>';
                            tableHtml += '</tr></thead><tbody>';
                            
                            $.each(productions, function(i, prod) {
                                tableHtml += '<tr>';
                                tableHtml += '<td><strong><a href="' + base_url + 'production/view/' + prod.id + '" target="_blank" style="color: #667eea; text-decoration: none;">' + prod.batch_code + '</a></strong></td>';
                                tableHtml += '<td>' + prod.recipe_name + '</td>';
                                tableHtml += '<td>' + prod.output_product_name + '</td>';
                                tableHtml += '<td class="text-right">' + parseFloat(prod.batch_quantity).toFixed(2) + '</td>';
                                tableHtml += '<td class="text-right">' + parseFloat(prod.total_output_qty).toFixed(2) + '</td>';
                                tableHtml += '<td class="text-right">' + parseFloat(prod.total_cost).toFixed(2) + ' Tk</td>';
                                tableHtml += '<td class="text-right">' + parseFloat(prod.cost_per_unit).toFixed(2) + ' Tk</td>';
                                tableHtml += '<td><span class="status-badge status-' + prod.status.toLowerCase() + '">' + prod.status + '</span></td>';
                                tableHtml += '<td>' + prod.approved_at.substring(0, 10) + '</td>';
                                tableHtml += '<td>' + prod.created_at.substring(0, 10) + '</td>';
                                tableHtml += '<td>' + prod.created_by_name + '</td>';
                                tableHtml += '</tr>';
                            });
                            
                            tableHtml += '</tbody></table></div>';
                        } else {
                            tableHtml += '<div class="no-data"><i class="fa fa-inbox fa-3x" style="color: #ccc;"></i><p>No productions found</p></div>';
                        }
                        
                        // Update the container
                        $('#productions-container').html(summaryHtml + tableHtml);
                        
                        showNotification('Filters applied successfully', 'success');
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

         * Reset filters
         */
        function resetFilters() {
            $('#filter-form')[0].reset();
            location.reload();
        }

        /**
         * Export data to CSV
         */
        function exportCSV() {
            var from_date = $('#from_date').val();
            var to_date = $('#to_date').val();

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

            // Export productions using GET request (bypasses CSRF)
            window.location.href = base_url + 'production_dashboard/export_csv?' +
                'from_date=' + encodeURIComponent(from_date_db) +
                '&to_date=' + encodeURIComponent(to_date_db);
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

        // Activate sidebar menu item
        $(document).ready(function() {
            $(".production-dashboard-active-li").addClass("active");
            // Also open parent menu (Production & Recipes)
            $(".recipe-list-active-li.recipe-active-li.production-list-active-li.production-active-li").addClass("active menu-open");
        });
    </script>

</body>

</html>
