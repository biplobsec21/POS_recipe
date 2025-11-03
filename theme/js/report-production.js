/**
 * Production Report JavaScript
 * Handles filtering, data loading, and export functionality
 */

$(document).ready(function () {
    // Initialize date pickers
    $('.datepicker').datepicker({
        autoclose: true,
        format: 'dd-mm-yyyy',
        todayHighlight: true,
        orientation: "bottom auto"
    });

    // Initialize select2
    $('.select2').select2({
        placeholder: "Select status",
        allowClear: true
    });

    // Set CSRF token for AJAX requests
    $.ajaxSetup({
        data: {
            [window.csrf_token_name]: window.csrf_hash
        }
    });

    // View button click handler
    $("#view").click(function () {
        show_report();
    });

    // Enter key support in form
    $("#report-form").keypress(function (e) {
        if (e.which == 13) {
            show_report();
            return false;
        }
    });

    // Reset form on reset button click
    $("#reset").click(function () {
        resetForm();
    });

    console.log("Production Report JS initialized successfully");
});

/**
 * Show production report based on filters
 */
function show_report() {
    var from_date = $("#from_date").val();
    var to_date = $("#to_date").val();
    var status = $("#status").val();

    console.log("Production Report - Filter values:", {
        from_date: from_date,
        to_date: to_date,
        status: status
    });

    // Basic validation
    if (!from_date) {
        toastr.error('Please select From Date!');
        $("#from_date").focus();
        return;
    }

    if (!to_date) {
        toastr.error('Please select To Date!');
        $("#to_date").focus();
        return;
    }

    // Validate date format and range
    if (!isValidDateFormat(from_date) || !isValidDateFormat(to_date)) {
        toastr.error('Please select valid dates in DD-MM-YYYY format!');
        return;
    }

    if (!isValidDateRange(from_date, to_date)) {
        toastr.error('From Date cannot be greater than To Date!');
        $("#from_date").focus();
        return;
    }

    // Show loading state
    setLoadingState(true);

    // Prepare data
    var formData = {
        from_date: from_date,
        to_date: to_date,
        status: status
    };

    console.log("Sending AJAX request to:", base_url + 'reports/get_production_report');

    // Make AJAX request
    $.ajax({
        url: base_url + 'reports/get_production_report',
        type: 'POST',
        dataType: 'json',
        data: formData,
        timeout: 30000, // 30 seconds timeout
        success: function (response) {
            setLoadingState(false);
            handleApiResponse(response);
        },
        error: function (xhr, status, error) {
            setLoadingState(false);
            handleAjaxError(xhr, status, error);
        }
    });
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

    // Check date validity
    if (year < 1000 || year > 3000 || month == 0 || month > 12) {
        return false;
    }

    var monthLength = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

    // Adjust for leap years
    if (year % 400 == 0 || (year % 100 != 0 && year % 4 == 0)) {
        monthLength[1] = 29;
    }

    return day > 0 && day <= monthLength[month - 1];
}

/**
 * Validate date range
 */
function isValidDateRange(fromDate, toDate) {
    try {
        var fromParts = fromDate.split('-');
        var toParts = toDate.split('-');

        var fromDateObj = new Date(fromParts[2], fromParts[1] - 1, fromParts[0]);
        var toDateObj = new Date(toParts[2], toParts[1] - 1, toParts[0]);

        return fromDateObj <= toDateObj;
    } catch (e) {
        console.error('Date validation error:', e);
        return false;
    }
}

/**
 * Set loading state for the report
 */
function setLoadingState(isLoading) {
    var viewBtn = $("#view");
    var originalText = viewBtn.html();

    if (isLoading) {
        viewBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');
        $("#tbodyid").html('<tr><td colspan="10" class="text-center loading-spinner"><i class="fa fa-spinner fa-spin"></i> Loading production data...</td></tr>');
        $('#tablefoot').hide();
    } else {
        viewBtn.prop('disabled', false).html('<i class="fa fa-search"></i> Show Report');
    }
}

/**
 * Handle API response
 */
function handleApiResponse(response) {
    console.log("API Response received:", response);

    if (response.success) {
        if (response.data && response.data.length > 0) {
            console.log("Data received, populating table with", response.data.length, "records");
            populate_table(response.data);
            updateReportInfo();
            toastr.success('Report loaded successfully! Found ' + response.data.length + ' record(s).');
        } else {
            console.log("No data found in response");
            var message = response.message || 'No production batches found for the selected criteria';
            showNoDataMessage(message);
            toastr.info(message);
        }
    } else {
        console.log("API returned success: false");
        var errorMsg = response.message || 'Failed to load production report';
        showErrorMessage(errorMsg);
        toastr.error(errorMsg);
    }
}

/**
 * Handle AJAX errors
 */
function handleAjaxError(xhr, status, error) {
    console.error('AJAX Error Details:');
    console.error('Status:', status);
    console.error('Error:', error);
    console.error('Response Text:', xhr.responseText);

    var errorMessage = 'Failed to load production report. ';

    if (xhr.status === 0) {
        errorMessage += 'Network connection error. Please check your internet connection.';
    } else if (xhr.status === 404) {
        errorMessage += 'Request URL not found. Please check the configuration.';
    } else if (xhr.status === 500) {
        errorMessage += 'Server error occurred. Please try again later.';
    } else if (status === 'timeout') {
        errorMessage += 'Request timeout. Please try again.';
    } else {
        errorMessage += 'Please try again. Error: ' + error;
    }

    showErrorMessage(errorMessage);
    toastr.error(errorMessage);
}

/**
 * Show no data message
 */
function showNoDataMessage(message) {
    $("#tbodyid").html('<tr><td colspan="10" class="text-center text-muted"><i class="fa fa-info-circle"></i> ' + message + '</td></tr>');
    $('#tablefoot').hide();
}

/**
 * Show error message
 */
function showErrorMessage(message) {
    $("#tbodyid").html('<tr><td colspan="10" class="text-center text-danger"><i class="fa fa-exclamation-triangle"></i> ' + message + '</td></tr>');
    $('#tablefoot').hide();
}

/**
 * Populate table with data
 */
function populate_table(data) {
    var html = '';
    var counter = 1;

    // Calculate totals
    var totalBatchQty = 0;
    var totalOutputQty = 0;
    var totalCost = 0;

    $.each(data, function (index, item) {
        var status_badge = getStatusBadge(item.status);

        // Parse quantities for totals
        var batchQty = parseFloat(item.batch_quantity) || 0;
        var outputQty = parseFloat(item.output_quantity) || 0;
        var cost = parseFloat(item.total_cost) || 0;

        totalBatchQty += batchQty;
        totalOutputQty += outputQty;
        totalCost += cost;

        html += '<tr>' +
            '<td>' + counter + '</td>' +
            '<td class="text-nowrap">' + (item.batch_code || 'N/A') + '</td>' +
            '<td>' + (item.recipe_name || 'N/A') + '</td>' +
            '<td class="text-right">' + formatNumber(batchQty) + '</td>' +
            '<td class="text-right">' + formatNumber(outputQty) + '</td>' +
            '<td class="text-right">' + formatCurrency(cost) + '</td>' +
            '<td class="text-right">' + formatCurrency(item.cost_per_unit || 0) + '</td>' +
            '<td class="text-nowrap">' + (item.created_at ? formatDate(item.created_at) : 'N/A') + '</td>' +
            '<td>' + status_badge + '</td>' +
            '<td>' + (item.created_by_name || 'N/A') + '</td>' +
            '</tr>';
        counter++;
    });

    $("#tbodyid").html(html);

    // Update totals row
    updateTotalsRow(totalBatchQty, totalOutputQty, totalCost);
    $('#tablefoot').show();
}

/**
 * Get status badge HTML
 */
function getStatusBadge(status) {
    var statusLower = status ? status.toLowerCase() : '';
    var badgeClass = 'label-default';
    var displayText = status || 'Unknown';

    switch (statusLower) {
        case 'approved':
            badgeClass = 'label-success';
            break;
        case 'draft':
            badgeClass = 'label-warning';
            break;
        case 'cancelled':
            badgeClass = 'label-danger';
            break;
    }

    return '<span class="label ' + badgeClass + ' status-badge">' + displayText + '</span>';
}

/**
 * Update totals row
 */
function updateTotalsRow(batchQty, outputQty, totalCost) {
    $('#total-batch-qty').text(formatNumber(batchQty));
    $('#total-output-qty').text(formatNumber(outputQty));
    $('#total-cost').text(formatCurrency(totalCost));
}

/**
 * Update report info
 */
function updateReportInfo() {
    var now = new Date();
    var dateTime = now.toLocaleDateString('en-IN') + ' ' + now.toLocaleTimeString('en-IN');
    $('#report-info').text('Report generated on: ' + dateTime);
}

/**
 * Format date for display
 */
function formatDate(dateString) {
    if (!dateString) return 'N/A';

    try {
        var date = new Date(dateString);
        if (isNaN(date.getTime())) {
            date = new Date(dateString.replace(/(\d{2})-(\d{2})-(\d{4})/, '$2/$1/$3'));
        }

        if (isNaN(date.getTime())) {
            return dateString;
        }

        var day = date.getDate().toString().padStart(2, '0');
        var month = (date.getMonth() + 1).toString().padStart(2, '0');
        var year = date.getFullYear();
        return day + '-' + month + '-' + year;
    } catch (e) {
        console.error('Date formatting error:', e);
        return dateString;
    }
}

/**
 * Format number with commas
 */
function formatNumber(number) {
    if (isNaN(number) || number === null || number === undefined) {
        return '0.00';
    }
    return parseFloat(number).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/**
 * Format currency amount
 */
function formatCurrency(amount) {
    if (isNaN(amount) || amount === null || amount === undefined) {
        return '0.00';
    }
    return parseFloat(amount).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/**
 * Export report functionality
 */
function exportReport(type) {
    var from_date = $("#from_date").val();
    var to_date = $("#to_date").val();
    var status = $("#status").val();

    // Basic validation
    if (!from_date || !to_date) {
        toastr.error('Please select both From Date and To Date for export!');
        return;
    }

    // Validate date range
    if (!isValidDateRange(from_date, to_date)) {
        toastr.error('From Date cannot be greater than To Date!');
        return;
    }

    console.log("Exporting report - Type:", type, "Filters:", {
        from_date: from_date,
        to_date: to_date,
        status: status
    });

    var url = base_url + 'reports/export_production_report/' + type +
        '?from_date=' + encodeURIComponent(from_date) +
        '&to_date=' + encodeURIComponent(to_date);

    if (status) {
        url += '&status=' + encodeURIComponent(status);
    }

    // Add cache busting parameter
    url += '&_=' + new Date().getTime();

    console.log("Export URL:", url);

    // Show loading message for exports
    toastr.info('Preparing export...');

    // Open in new window/tab
    var exportWindow = window.open(url, '_blank');

    // Check if window was blocked
    if (!exportWindow || exportWindow.closed || typeof exportWindow.closed == 'undefined') {
        toastr.warning('Popup was blocked. Please allow popups for this site and try again.');
    }
}

/**
 * Reset form to default values
 */
function resetForm() {
    $('#from_date').val("<?php echo show_date(date('d - m - Y', strtotime(' - 30 days'))); ?>");
    $('#to_date').val("<?php echo show_date(date('d - m - Y')); ?>");
    $('#status').val('').trigger('change');
    $('#tbodyid').html('<tr><td colspan="10" class="text-center text-muted"><i class="fa fa-info-circle"></i> Please select filters and click "Show Report" to view production data</td></tr>');
    $('#tablefoot').hide();
    toastr.info('Filters have been reset');
}

/**
 * Print report
 * Note: This uses the export functionality with print type
 */
function printReport() {
    exportReport('print');
}

// Initialize when document is ready
$(document).ready(function () {
    console.log("Production Report JS loaded successfully");
    console.log("Configuration:", {
        base_url: base_url,
        csrf_token_name: csrf_token_name,
        currency_symbol: currency_symbol
    });
    show_report();
    // Auto-load report after 1 second (optional)
    // setTimeout(function() { 
    //     console.log("Auto-loading report...");
    //     show_report(); 
    // }, 1000);
});