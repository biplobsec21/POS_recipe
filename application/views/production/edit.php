<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html>

<head>
    <!-- TABLES CSS CODE -->
    <?php $this->load->view('comman/code_css_form'); ?>
    <style type="text/css">
        table.table-bordered>thead>tr>th {
            text-align: center;
        }

        .table>tbody>tr>td,
        .table>tbody>tr>th,
        .table>tfoot>tr>td,
        .table>tfoot>tr>th,
        .table>thead>tr>td,
        .table>thead>tr>th {
            padding-left: 2px;
            padding-right: 2px;
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
                    <small>Edit Production Batch</small>
                </h1>

                <ol class="breadcrumb">
                    <li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="<?php echo $base_url; ?>production">Production Batches</a></li>
                    <li class="active"><?= $page_title; ?></li>
                </ol>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="row">
                    <!-- ********** ALERT MESSAGE START******* -->
                    <?php $this->load->view('comman/code_flashdata'); ?>
                    <!-- ********** ALERT MESSAGE END******* -->
                    <!-- right column -->
                    <div class="col-md-12">
                        <!-- Horizontal Form -->
                        <div class="box box-info ">
                            <div class="box-header with-border">
                                <h3 class="box-title">Please update the details below</h3>
                            </div>
                            <!-- /.box-header -->
                            <!-- form start -->
                            <?= form_open('#', array('class' => 'form-horizontal', 'id' => 'production-form', 'enctype' => 'multipart/form-data', 'method' => 'POST')); ?>
                            <input type="hidden" id="base_url" value="<?php echo isset($base_url) ? $base_url : base_url(); ?>">
                            <input type="hidden" id="production_id" name="production_id" value="<?= $production->id; ?>">

                            <div class="box-body">
                                <div class="form-group">
                                    <label for="recipe_id" class="col-sm-2 control-label">Recipe<label class="text-danger">*</label></label>
                                    <div class="col-sm-4">
                                        <select class="form-control select2" id="recipe_id" name="recipe_id" style="width: 100%;">
                                            <option value="">-Select-</option>
                                            <?php foreach ($recipes as $recipe): ?>
                                                <option value="<?= $recipe->id; ?>" <?= ($production->recipe_id == $recipe->id) ? 'selected' : ''; ?>>
                                                    <?= $recipe->recipe_name; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span id="recipe_id_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="batch_quantity" class="col-sm-2 control-label">Batch Quantity<label class="text-danger">*</label></label>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control only_currency" id="batch_quantity" name="batch_quantity" placeholder="" value="<?= $production->batch_quantity; ?>" autofocus>
                                        <span id="batch_quantity_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-sm-offset-2 col-sm-4">
                                        <button type="button" class="btn btn-info" id="check_availability">Check Stock Availability</button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="notes" class="col-sm-2 control-label">Notes</label>
                                    <div class="col-sm-4">
                                        <textarea class="form-control" id="notes" name="notes" placeholder=""><?= $production->notes; ?></textarea>
                                        <span id="notes_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="box">
                                        <div class="box-info">
                                            <div class="box-header">
                                                <h3 class="box-title">Ingredients Required</h3>
                                            </div>
                                            <div class="box-body">
                                                <div class="table-responsive" style="width: 100%">
                                                    <table class="table table-hover table-bordered" style="width:100%" id="ingredients_table">
                                                        <thead class="custom_thead">
                                                            <tr class="bg-primary">
                                                                <th style="width:30%">Ingredient</th>
                                                                <th style="width:20%">Required Quantity</th>
                                                                <th style="width:20%">Available Quantity</th>
                                                                <th style="width:20%">Unit</th>
                                                                <th style="width:10%">Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <!-- Ingredients will be loaded here dynamically -->
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- /.box-body -->

                            <div class="box-footer">
                                <div class="col-sm-8 col-sm-offset-2 text-center">
                                    <div class="col-md-3 col-md-offset-3">
                                        <button type="button" id="update" class="btn btn-block btn-success" title="Update Production">Update</button>
                                    </div>
                                    <div class="col-sm-3">
                                        <a href="<?= base_url('production'); ?>">
                                            <button type="button" class="col-sm-3 btn btn-block btn-warning close_btn" title="Go to Productions List">Close</button>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <!-- /.box-footer -->
                            <?= form_close(); ?>
                        </div>
                        <!-- /.box -->
                    </div>
                    <!--/.col (right) -->
                </div>
                <!-- /.row -->
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <?php $this->load->view('footer'); ?>
        <div class="control-sidebar-bg"></div>
    </div>
    <!-- ./wrapper -->

    <!-- SOUND CODE -->
    <?php $this->load->view('comman/code_js_sound'); ?>
    <!-- FORM JS CODE -->
    <?php $this->load->view('comman/code_js_form'); ?>

    <script src="<?php echo isset($theme_link) ? $theme_link : base_url('theme/'); ?>js/production.js"></script>
    <script>
        var base_url = "<?= base_url(); ?>";

        // Check stock availability
        $("#check_availability").click(function() {
            var recipe_id = $("#recipe_id").val();
            var batch_quantity = $("#batch_quantity").val();

            if (!recipe_id) {
                toastr.error('Please select a recipe!');
                return;
            }

            if (!batch_quantity || batch_quantity <= 0) {
                toastr.error('Please enter a valid batch quantity!');
                return;
            }

            // Show loading state
            var checkBtn = $("#check_availability");
            var originalText = checkBtn.html();
            checkBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Checking...');

            $.ajax({
                url: base_url + 'production/get_recipe_details/' + recipe_id,
                method: 'GET',
                dataType: 'json',
                data: {
                    batch_quantity: batch_quantity
                },
                success: function(data) {
                    // Reset button
                    checkBtn.prop('disabled', false).html(originalText);

                    // Clear existing table rows
                    $("#ingredients_table tbody").empty();

                    // Add new rows
                    if (data.ingredients && data.ingredients.length > 0) {
                        $.each(data.ingredients, function(index, ingredient) {
                            var required_qty = parseFloat(ingredient.required_qty) * parseFloat(batch_quantity);
                            var available_qty = parseFloat(ingredient.available_qty);
                            var status = '';
                            var status_class = '';

                            if (available_qty >= required_qty) {
                                status = 'In Stock';
                                status_class = 'label label-success';
                            } else {
                                status = 'Out of Stock';
                                status_class = 'label label-danger';
                            }

                            var row = '<tr>' +
                                '<td>' + ingredient.item_name + '</td>' +
                                '<td class="text-right">' + required_qty.toFixed(2) + '</td>' +
                                '<td class="text-right">' + available_qty.toFixed(2) + '</td>' +
                                '<td>' + ingredient.unit + '</td>' +
                                '<td class="text-center"><span class="' + status_class + '">' + status + '</span></td>' +
                                '</tr>';

                            $("#ingredients_table tbody").append(row);
                        });
                    } else {
                        $("#ingredients_table tbody").append('<tr><td colspan="5" class="text-center">No ingredients found for this recipe</td></tr>');
                    }
                },
                error: function() {
                    // Reset button
                    checkBtn.prop('disabled', false).html(originalText);
                    toastr.error('Failed to check stock availability!');
                }
            });
        });

        // Update production
        $("#update").click(function() {
            var recipe_id = $("#recipe_id").val();
            var batch_quantity = $("#batch_quantity").val();
            var production_id = $("#production_id").val();

            // Basic validation
            if (!recipe_id) {
                toastr.error('Please select a recipe!');
                $("#recipe_id").focus();
                return;
            }

            if (!batch_quantity || parseFloat(batch_quantity) <= 0) {
                toastr.error('Please enter a valid batch quantity!');
                $("#batch_quantity").focus();
                return;
            }

            // Show loading state
            var updateBtn = $("#update");
            var originalText = updateBtn.html();
            updateBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');

            // Submit form via AJAX
            $.ajax({
                url: base_url + 'production/update/' + production_id,
                type: 'POST',
                data: $('#production-form').serialize(),
                success: function(response) {
                    // Handle text response
                    if (response.trim() === 'success') {
                        toastr.success('Production batch updated successfully!');
                        // Redirect to list page after 1 second
                        setTimeout(function() {
                            window.location.href = base_url + 'production';
                        }, 1000);
                    } else {
                        toastr.error(response);
                        updateBtn.prop('disabled', false).html(originalText);
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('Failed to update production batch. Please try again.');
                    updateBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Load ingredients on page load if recipe is already selected
        $(document).ready(function() {
            var recipe_id = $("#recipe_id").val();
            var batch_quantity = $("#batch_quantity").val();

            if (recipe_id && batch_quantity) {
                $("#check_availability").click();
            }
        });

        // Make sidebar menu highlighter/selector
        $(".production-active-li").addClass("active");
    </script>
</body>

</html>