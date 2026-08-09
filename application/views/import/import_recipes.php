<!DOCTYPE html>
<html>

<head>
    <!-- TABLES CSS CODE -->
    <?php $this->load->view('comman/code_css_form'); ?>
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
                    <small>Import Recipes</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="<?php echo $base_url; ?>recipe"><?= $this->lang->line('recipes_list'); ?></a></li>
                    <li class="active"><?= $page_title; ?></li>
                </ol>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">Please Enter Valid Data</h3>
                            </div>
                            <!-- /.box-header -->
                            <form class="form-horizontal" id="import-form" enctype="multipart/form-data" method="POST">
                                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                                <input type="hidden" id="base_url" value="<?php echo $base_url; ?>">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="import_file" class="col-sm-2 control-label">Import Recipes<label class="text-danger">*</label></label>
                                        <div class="col-sm-4">
                                            <input type="file" id="import_file" name="import_file">
                                            <span id="import_file_msg" style="display:block;" class="text-danger">
                                                Note: File must be in CSV format. One row per ingredient - recipe details are repeated on every ingredient row.
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.box-body -->
                                <div class="box-footer">
                                    <div class="col-sm-8 text-center">
                                        <div class="col-md-3">
                                            <button type="button" id="import" class="btn btn-block btn-success" title="Import Recipes"><i class="fa fa-arrow-circle-o-left "></i> Import</button>
                                        </div>
                                        <div class="col-sm-3">
                                            <a href="<?= base_url('dashboard'); ?>">
                                                <button type="button" class="col-sm-3 btn btn-block btn-warning close_btn" title="Go Dashboard">Close</button>
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

            <section class="content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-header">
                                <h3 class="box-title"><?= $this->lang->line('import_instructions'); ?></h3>
                                <a href="<?= base_url(); ?>uploads/csv/examples/import-recipes-example.csv">
                                    <button type="button" class="btn btn-info pull-right btnExport" title="Download Example Format"><?= $this->lang->line('download_example_format'); ?></button>
                                </a>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body table-responsive no-padding">
                                <table class="table table-bordered table-hover" id="report-data">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?= $this->lang->line('column_name'); ?></th>
                                            <th><?= $this->lang->line('value'); ?></th>
                                            <th><?= $this->lang->line('description'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>recipe_name</td>
                                            <td><span class="label label-success"><?= $this->lang->line('required'); ?></span></td>
                                            <td>Unique recipe name. Rows sharing the same name are grouped as one recipe.</td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>output_product</td>
                                            <td><span class="label label-success"><?= $this->lang->line('required'); ?></span></td>
                                            <td>Item NAME or item CODE of the finished product (must already exist in Items).</td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td>yield_quantity</td>
                                            <td><span class="label label-success"><?= $this->lang->line('required'); ?></span></td>
                                            <td>Number of units the recipe produces (e.g. 46).</td>
                                        </tr>
                                        <tr>
                                            <td>4</td>
                                            <td>overhead_cost</td>
                                            <td><span class="label label-default"><?= $this->lang->line('optional'); ?></span></td>
                                            <td>Other / overhead cost. Default 0.</td>
                                        </tr>
                                        <tr>
                                            <td>5</td>
                                            <td>overhead_cost_type</td>
                                            <td><span class="label label-default"><?= $this->lang->line('optional'); ?></span></td>
                                            <td>"fixed" (cost for whole batch) or "per_unit" (cost x yield). Default "fixed".</td>
                                        </tr>
                                        <tr>
                                            <td>6</td>
                                            <td>notes</td>
                                            <td><span class="label label-default"><?= $this->lang->line('optional'); ?></span></td>
                                            <td>Any notes for the recipe.</td>
                                        </tr>
                                        <tr>
                                            <td>7</td>
                                            <td>ingredient_item</td>
                                            <td><span class="label label-success"><?= $this->lang->line('required'); ?></span></td>
                                            <td>Item NAME or item CODE of one ingredient (must already exist in Items).</td>
                                        </tr>
                                        <tr>
                                            <td>8</td>
                                            <td>ingredient_quantity</td>
                                            <td><span class="label label-success"><?= $this->lang->line('required'); ?></span></td>
                                            <td>Quantity of that ingredient needed for one batch.</td>
                                        </tr>
                                        <tr>
                                            <td>9</td>
                                            <td>ingredient_unit</td>
                                            <td><span class="label label-default"><?= $this->lang->line('optional'); ?></span></td>
                                            <td>Unit of the ingredient (KG, PCS, ...). If blank, the item's default unit is used.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.box-body -->
                        </div>
                        <!-- /.box -->
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

    <!-- SOUND CODE -->
    <?php $this->load->view('comman/code_js_sound'); ?>
    <!-- TABLES CODE -->
    <?php $this->load->view('comman/code_js_form'); ?>

    <script type="text/javascript">
        //Post the file
        $("#import").on("click", function(e) {
            var base_url = $("#base_url").val();
            if ($("#import_file").val() == '') {
                toastr["warning"]("Please select file to Import!");
                failed.currentTime = 0;
                failed.play();
                return;
            }

            if (confirm("Are you sure ?")) {
                e.preventDefault();
                data = new FormData($('#import-form')[0]); //form name
                /*Check XSS Code*/
                if (!xss_validation(data)) {
                    return false;
                }

                $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
                $("#import").attr('disabled', true); //Enable Save or Update button
                $.ajax({
                    type: 'POST',
                    url: base_url + 'import/import_recipes_csv',
                    data: data,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(result) {
                        if (result == "success") {
                            window.location = base_url + "recipe";
                        } else if (result == "failed") {
                            toastr["error"]("Sorry! Failed to save Record.Try again!");
                        } else {
                            toastr["error"](result);
                        }
                        $("#import").attr('disabled', false); //Enable Save or Update button
                        $(".overlay").remove();
                    }
                });
            }
        });

        // Make sidebar menu highlighter/selector
        $(".recipe-active-li").addClass("active");
    </script>
</body>

</html>