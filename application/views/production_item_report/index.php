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
            <section class="content-header" style="border-botton:one !important;">
                <h1>
                    <?= $page_title; ?>
                    <small>Item usage analysis in productions</small>
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
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Item</label>
                                        <div class="col-sm-8">
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

                <!-- ITEM USAGE REPORT -->
                <div class="row">
                    <div class="col-md-12">
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
            // Pre-load all items via AJAX and store in Select2
            var loadItems = function(callback) {
                $.ajax({
                    url: base_url + 'production_item_report/get_items_json?search=',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if (callback) callback(data);
                    },
                    error: function(error) {
                        console.error('Failed to load items:', error);
                        if (callback) callback({results: []});
                    }
                });
            };

            // Initialize Select2
            $('#item_id').select2({
                placeholder: "Search and select item",
                allowClear: true,
                width: '100%',
                ajax: {
                    url: base_url + 'production_item_report/get_items_json',
                    type: 'GET',
                    dataType: 'json',
                    delay: 300,
                    data: function(params) {
                        return { 
                            search: params.term || ''
                        };
                    },
                    processResults: function(data) {
                        console.log('Select2 processing results:', data);
                        return { 
                            results: data.results || []
                        };
                    },
                    error: function(error) {
                        console.error('Select2 AJAX Error:', error);
                    }
                },
                minimumInputLength: 0
            });

            // Load items immediately when page loads
            loadItems(function(data) {
                console.log('Initial items loaded:', data);
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
         * Apply filters to update item usage report
         */
        function applyFilters() {
            var from_date = $('#from_date').val();
            var to_date = $('#to_date').val();
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

            if (!item_id) {
                showNotification('Please select an item', 'error');
                return;
            }

            // Convert dates to DB format (YYYY-MM-DD)
            var from_date_db = convertToDbFormat(from_date);
            var to_date_db = convertToDbFormat(to_date);

            // Update item usage report
            updateItemUsageTab(item_id, from_date_db, to_date_db);
        }

        /**
         * Update item usage report
         */
        function updateItemUsageTab(item_id, from_date, to_date) {
            var container = $('#item-usage-container');
            container.html('<div class="loading-spinner"><i class="fa fa-spinner fa-spin fa-2x"></i> Loading...</div>');

            $.ajax({
                type: 'POST',
                url: base_url + 'production_item_report/get_item_usage',
                data: {
                    item_id: item_id,
                    from_date: from_date,
                    to_date: to_date
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
                        html += '</div>';

                        if (report.length > 0) {
                            html += '<div class="table-responsive"><table class="table table-bordered table-striped">';
                            html += '<thead class="bg-primary"><tr>';
                            html += '<th>Batch Code</th><th>Recipe</th><th>Batch Qty</th>';
                            html += '<th>Qty Per Batch</th><th>Total Consumed</th>';
                            html += '<th>Created Date</th><th>Created By</th>';
                            html += '</tr></thead><tbody>';

                            $.each(report, function(i, item) {
                                html += '<tr>';
                                html += '<td><strong>' + item.batch_code + '</strong></td>';
                                html += '<td>' + item.recipe_name + '</td>';
                                html += '<td class="text-right">' + parseFloat(item.batch_quantity).toFixed(2) + '</td>';
                                html += '<td class="text-right">' + parseFloat(item.quantity_per_batch).toFixed(2) + '</td>';
                                html += '<td class="text-right"><strong>' + parseFloat(item.total_consumed).toFixed(2) + '</strong></td>';
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
                        showNotification('Report loaded successfully', 'success');
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
         * Reset filters
         */
        function resetFilters() {
            $('#filter-form')[0].reset();
            $('#item-usage-container').html(
                '<div class="no-data">' +
                '<i class="fa fa-inbox fa-3x" style="color: #ccc;"></i>' +
                '<p>Select an item and apply filters to view usage report</p>' +
                '</div>'
            );
            showNotification('Filters reset', 'success');
        }

        /**
         * Export data to CSV
         */
        function exportData() {
            var from_date = $('#from_date').val();
            var to_date = $('#to_date').val();
            var item_id = $('#item_id').val();

            if (!from_date || !to_date) {
                showNotification('Please select both dates', 'error');
                return;
            }

            if (!isValidDateFormat(from_date) || !isValidDateFormat(to_date)) {
                showNotification('Please select valid dates in DD-MM-YYYY format', 'error');
                return;
            }

            if (!item_id) {
                showNotification('Please select an item to export', 'error');
                return;
            }

            var from_date_db = convertToDbFormat(from_date);
            var to_date_db = convertToDbFormat(to_date);

            // Export item usage using GET request (bypasses CSRF)
            window.location.href = base_url + 'production_item_report/export_item_usage?' +
                'item_id=' + encodeURIComponent(item_id) +
                '&from_date=' + encodeURIComponent(from_date_db) +
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
            $(".production-item-report-active-li").addClass("active");
            // Also open parent menu (Production & Recipes)
            $(".recipe-list-active-li.recipe-active-li.production-list-active-li.production-active-li").addClass("active menu-open");
        });
    </script>

</body>

</html>
