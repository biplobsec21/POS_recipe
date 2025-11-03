<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html>

<head>
    <!-- TABLES CSS CODE -->
    <?php $this->load->view('comman/code_css_datatable'); ?>
    <style>
        .recipe-details {
            font-size: 12px;
            line-height: 1.4;
        }

        .cost-details {
            font-size: 12px;
            line-height: 1.4;
        }

        .status-label {
            font-size: 12px;
            padding: 4px 8px;
        }
    </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

        <?php $this->load->view('sidebar'); ?>
        <?php $CI = &get_instance(); ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>
                    <?= $page_title; ?>
                    <small>View Productions</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active"><?= $page_title; ?></li>
                </ol>
            </section>

            <!-- Main content -->
            <?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
            <input type="hidden" id='base_url' value="<?= isset($base_url) ? $base_url : base_url(); ?>">
            <section class="content">
                <div class="row">
                    <!-- ********** ALERT MESSAGE START******* -->
                    <?php $this->load->view('comman/code_flashdata'); ?>
                    <!-- ********** ALERT MESSAGE END******* -->
                    <div class="col-xs-12">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title"><?= $page_title; ?></h3>
                                <?php if ($CI->permissions('production_add')) { ?>
                                    <div class="box-tools">
                                        <a class="btn btn-block btn-info" href="<?= base_url('production/add'); ?>">
                                            <i class="fa fa-plus"></i> Add Production</a>
                                    </div>
                                <?php } ?>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <table id="example2" class="table table-bordered table-striped" width="100%">
                                    <thead class="bg-primary ">
                                        <tr>
                                            <th class="text-center">
                                                <input type="checkbox" class="group_check checkbox">
                                            </th>
                                            <th>Batch Code</th>
                                            <th>Recipe Name</th>
                                            <th>Batch Qty</th>
                                            <th>Recipe Details</th>
                                            <th>Cost Details</th>
                                            <th>Production Date</th>
                                            <th>Status</th>
                                            <th>Created By</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>

                                </table>
                            </div>
                            <!-- /.box-body -->
                        </div>
                        <!-- /.box -->
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
            </section>
            <!-- /.content -->
            <?= form_close(); ?>
        </div>
        <!-- /.content-wrapper -->
        <?php $this->load->view('footer'); ?>
        <div class="control-sidebar-bg"></div>
    </div>
    <!-- ./wrapper -->

    <!-- SOUND CODE -->
    <?php $this->load->view('comman/code_js_sound'); ?>
    <!-- TABLES CODE -->
    <?php $this->load->view('comman/code_js_datatable'); ?>

    <script type="text/javascript">
        $(document).ready(function() {
            //datatables
            var table = $('#example2').DataTable({

                /* FOR EXPORT BUTTONS START*/
                dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>tip',
                buttons: {
                    buttons: [{
                            className: 'btn bg-red color-palette btn-flat hidden delete_btn pull-left',
                            text: 'Delete',
                            action: function(e, dt, node, config) {
                                multi_delete();
                            }
                        },
                        {
                            extend: 'copy',
                            className: 'btn bg-teal color-palette btn-flat',
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6, 7, 8]
                            }
                        },
                        {
                            extend: 'excel',
                            className: 'btn bg-teal color-palette btn-flat',
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6, 7, 8]
                            }
                        },
                        {
                            extend: 'pdf',
                            className: 'btn bg-teal color-palette btn-flat',
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6, 7, 8]
                            }
                        },
                        {
                            extend: 'print',
                            className: 'btn bg-teal color-palette btn-flat',
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6, 7, 8]
                            }
                        },
                        {
                            extend: 'csv',
                            className: 'btn bg-teal color-palette btn-flat',
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6, 7, 8]
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

                "processing": true, //Feature control the processing indicator.
                "serverSide": true, //Feature control DataTables' server-side processing mode.
                "order": [], //Initial no order.
                "responsive": true,
                language: {
                    processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>'
                },
                // Load data for the table's content from an Ajax source
                "ajax": {
                    "url": "<?php echo site_url('production/ajax_list') ?>",
                    "type": "POST",

                    complete: function(data) {
                        $('.column_checkbox').iCheck({
                            checkboxClass: 'icheckbox_square-orange',
                            radioClass: 'iradio_square-orange',
                            increaseArea: '10%' // optional
                        });
                        call_code();
                    },

                },

                //Set column definition initialisation properties.
                "columnDefs": [{
                        "targets": [0, 9], //first column / numbering column
                        "orderable": false, //set not orderable
                    },
                    {
                        "targets": [0],
                        "className": "text-center",
                    },
                    {
                        "targets": [4, 5],
                        "className": "recipe-details",
                    },
                    {
                        "targets": [7],
                        "className": "text-center",
                    }

                ],
            });
            new $.fn.dataTable.FixedHeader(table);
        });

        // Function to view production details
        function view_production_details(id) {
            window.location.href = '<?= base_url('production/view/') ?>' + id;
        }

        // Function to approve production
        function approve_production(id) {
            if (confirm('Are you sure you want to approve this production batch?')) {
                $.ajax({
                    url: '<?= base_url('production/approve/') ?>' + id,
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        if (response === 'success') {
                            alert('Production batch approved successfully!');
                            $('#example2').DataTable().ajax.reload();
                        } else {
                            alert('Error: ' + response);
                        }
                    },
                    error: function() {
                        alert('Error approving production batch.');
                    }
                });
            }
        }

        // Function to delete production
        function delete_production(id) {
            if (confirm('Are you sure you want to delete this production batch?')) {
                $.ajax({
                    url: '<?= base_url('production/delete/') ?>' + id,
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        if (response === 'success') {
                            alert('Production batch deleted successfully!');
                            $('#example2').DataTable().ajax.reload();
                        } else {
                            alert('Error: ' + response);
                        }
                    },
                    error: function() {
                        alert('Error deleting production batch.');
                    }
                });
            }
        }
    </script>
    <script src="<?php echo isset($theme_link) ? $theme_link : base_url('theme/'); ?>js/production.js"></script>
    <!-- Make sidebar menu highlighter/selector -->
    <script>
        $(".production-list-active-li").addClass("active");
    </script>
</body>

</html>