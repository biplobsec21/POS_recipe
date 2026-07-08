<!DOCTYPE html>
<html>

<head>
   <!-- TABLES CSS CODE -->
   <?php include "comman/code_css_datatable.php"; ?>
   <!-- bootstrap datepicker -->
   <link rel="stylesheet" href="<?php echo $theme_link; ?>plugins/datepicker/datepicker3.css">
   <style type="text/css">
      .small-box h3 { font-size: 28px !important; }
      .dataTables_wrapper .dataTables_paginate .paginate_button { padding: 5px 10px !important; margin: 2px !important; }
      .dataTables_wrapper .dataTables_paginate .paginate_button.current { background: #3c8dbc !important; color: white !important; border: 1px solid #3c8dbc !important; }
      .dataTables_wrapper .dataTables_paginate .paginate_button:hover { background: #5caddb !important; color: white !important; }
   </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
   <div class="wrapper">

      <?php include "sidebar.php"; ?>

      <?php
      $total_records  = $this->db->query("SELECT COUNT(*) AS total FROM db_damage")->row()->total;
      $total_qty      = $this->db->query("SELECT COALESCE(SUM(total_qty),0) AS tq FROM db_damage")->row()->tq;
      $total_value    = $this->db->query("SELECT COALESCE(SUM(total_value),0) AS tv FROM db_damage")->row()->tv;
      $approved_count = $this->db->query("SELECT COUNT(*) AS cnt FROM db_damage WHERE status='approved'")->row()->cnt;
      ?>

      <div class="content-wrapper">
         <section class="content-header">
            <h1>
               <?= $page_title; ?>
               <small>View / Search Damage Records</small>
            </h1>
            <ol class="breadcrumb">
               <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
               <li class="active"><?= $page_title; ?></li>
            </ol>
         </section>

         <?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
         <input type="hidden" id="base_url" value="<?= $base_url; ?>">

         <section class="content">
            <!-- Stat boxes -->
            <div class="row">
               <div class="col-lg-3 col-xs-6">
                  <div class="small-box bg-aqua">
                     <div class="inner">
                        <h3><?= $total_records; ?></h3>
                        <p>Total Records</p>
                     </div>
                     <div class="icon"><i class="ion ion-android-warning"></i></div>
                     <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                  </div>
               </div>
               <div class="col-lg-3 col-xs-6">
                  <div class="small-box bg-yellow">
                     <div class="inner">
                        <h3><?= number_format($total_qty, 2); ?></h3>
                        <p>Total Damaged Qty</p>
                     </div>
                     <div class="icon"><i class="fa fa-cubes"></i></div>
                     <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                  </div>
               </div>
               <div class="col-lg-3 col-xs-6">
                  <div class="small-box bg-red">
                     <div class="inner">
                        <h3><?= $CI->currency($total_value, true); ?></h3>
                        <p>Total Damage Value</p>
                     </div>
                     <div class="icon"><i class="fa fa-money"></i></div>
                     <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                  </div>
               </div>
               <div class="col-lg-3 col-xs-6">
                  <div class="small-box bg-green">
                     <div class="inner">
                        <h3><?= $approved_count; ?></h3>
                        <p>Approved Records</p>
                     </div>
                     <div class="icon"><i class="fa fa-check-circle"></i></div>
                     <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                  </div>
               </div>
            </div>
            <!-- /.row -->

            <div class="row">
               <?php include "comman/code_flashdata.php"; ?>

               <div class="col-xs-12">
                  <div class="box">
                     <div class="box-header with-border">

                        <div class="row">
                           <div class="col-md-12">
                              <div class="col-md-2 pull-right">
                                 <?php if ($CI->permissions('damage_add')) { ?>
                                    <div class="box-tools">
                                       <a class="btn btn-block btn-info" href="<?php echo $base_url; ?>damage/add">
                                          <i class="fa fa-plus"></i> New Damage</a>
                                    </div>
                                 <?php } ?>
                              </div>
                           </div>
                        </div>

                        <!-- Filters -->
                        <div class="row" style="margin-top:10px;">
                           <div class="col-md-12">

                              <div class="col-md-3">
                                 <div class="form-group">
                                    <label>Damage Type</label>
                                    <select class="form-control select2" id="filter_damage_type" name="filter_damage_type" style="width:100%;">
                                       <option value="">- All Types -</option>
                                       <option value="general">General</option>
                                       <option value="expired">Expired</option>
                                       <option value="broken">Broken</option>
                                       <option value="lost">Lost</option>
                                       <option value="other">Other</option>
                                    </select>
                                 </div>
                              </div>

                              <div class="col-md-3">
                                 <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control select2" id="filter_status" name="filter_status" style="width:100%;">
                                       <option value="">- All Status -</option>
                                       <option value="draft">Draft</option>
                                       <option value="approved">Approved</option>
                                    </select>
                                 </div>
                              </div>

                              <div class="col-md-3">
                                 <div class="form-group">
                                    <label>From Date</label>
                                    <div class="input-group date">
                                       <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                       <input type="text" class="form-control pull-right datepicker"
                                              id="damage_from_date" name="damage_from_date">
                                    </div>
                                 </div>
                              </div>

                              <div class="col-md-3">
                                 <div class="form-group">
                                    <label>To Date</label>
                                    <div class="input-group date">
                                       <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                       <input type="text" class="form-control pull-right datepicker"
                                              id="damage_to_date" name="damage_to_date">
                                    </div>
                                 </div>
                              </div>

                           </div>
                        </div>
                        <!-- /.filters -->

                     </div>
                     <!-- /.box-header -->

                     <div class="box-body">
                        <table id="damage_datatable" class="table table-bordered table-striped" width="100%">
                           <thead class="bg-primary">
                              <tr>
                                 <th class="text-center">
                                    <input type="checkbox" class="group_check checkbox">
                                 </th>
                                 <th>Date</th>
                                 <th>Damage Code</th>
                                 <th>Type</th>
                                 <th>Items</th>
                                 <th>Warehouse</th>
                                 <th>Total Qty</th>
                                 <th>Total Value</th>
                                 <th>Status</th>
                                 <th>Created By</th>
                                 <th>Action</th>
                              </tr>
                           </thead>
                           <tbody></tbody>
                           <tfoot>
                              <tr class="bg-gray">
                                 <th colspan="6" style="text-align:right">Total</th>
                                 <th></th><!-- qty -->
                                 <th></th><!-- value -->
                                 <th></th>
                                 <th></th>
                                 <th></th>
                              </tr>
                           </tfoot>
                        </table>
                     </div>
                     <!-- /.box-body -->
                  </div>
                  <!-- /.box -->
               </div>
            </div>
            <!-- /.row -->
         </section>
         <!-- /.content -->
         <?= form_close(); ?>
      </div>
      <!-- /.content-wrapper -->

      <?php include "footer.php"; ?>
      <div class="control-sidebar-bg"></div>
   </div>
   <!-- ./wrapper -->

   <?php include "comman/code_js_sound.php"; ?>
   <?php include "comman/code_js_datatable.php"; ?>
   <script src="<?php echo $theme_link; ?>plugins/datepicker/bootstrap-datepicker.js"></script>

   <script type="text/javascript">
      $('.datepicker').datepicker({
         autoclose: true,
         format: 'dd-mm-yyyy',
         todayHighlight: true
      });

      function load_datatable() {
         var table = $('#damage_datatable').DataTable({
            dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10"B>>>tip',
            buttons: {
               buttons: [
                  {
                     className: 'btn bg-red color-palette btn-flat hidden delete_btn pull-left',
                     text: 'Delete',
                     action: function (e, dt, node, config) { multi_delete_damage(); }
                  },
                  { extend: 'copy',  className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6,7,8,9] } },
                  { extend: 'excel', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6,7,8,9] } },
                  { extend: 'pdf',   className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6,7,8,9] } },
                  { extend: 'print', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6,7,8,9] } },
                  { extend: 'csv',   className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6,7,8,9] } },
                  { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat', text: 'Columns' }
               ]
            },
            pageLength: 50,
            processing: true,
            serverSide: true,
            order: [],
            responsive: true,
            deferRender: true,
            searchDelay: 600,
            paging: true,
            lengthMenu: [[10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, 'All']],
            pagingType: 'full_numbers',
            language: {
               processing: '<div class="text-primary" style="position:relative;z-index:100;">Processing...</div>',
               paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' },
               info: 'Showing _START_ to _END_ of _TOTAL_ entries',
               infoEmpty: 'Showing 0 to 0 of 0 entries',
               infoFiltered: '(filtered from _MAX_ total entries)'
            },
            ajax: {
               url: '<?php echo site_url('damage/ajax_list'); ?>',
               type: 'POST',
               data: function (d) {
                  d.filter_damage_type = $('#filter_damage_type').val();
                  d.filter_status      = $('#filter_status').val();
                  d.damage_from_date   = $('#damage_from_date').val();
                  d.damage_to_date     = $('#damage_to_date').val();
               },
               cache: true,
               timeout: 30000,
               complete: function () {
                  $('.column_checkbox').iCheck({
                     checkboxClass: 'icheckbox_square-orange',
                     radioClass: 'iradio_square-orange',
                     increaseArea: '10%'
                  });
                  call_code();
               }
            },
            columnDefs: [
               { targets: [0, 10], orderable: false },
               { targets: [0], className: 'text-center' }
            ],
            footerCallback: function (row, data, start, end, display) {
               var api = this.api();
               var intVal = function (i) {
                  return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
               };
               var totalQty   = api.column(6, { page: 'none' }).data().reduce(function (a, b) { return intVal(a) + intVal(b); }, 0);
               var totalValue = api.column(7, { page: 'none' }).data().reduce(function (a, b) { return intVal(a) + intVal(b); }, 0);
               $(api.column(6).footer()).html(app_number_format(totalQty));
               $(api.column(7).footer()).html(app_number_format(totalValue));
            }
         });
         new $.fn.dataTable.FixedHeader(table);
      }

      $(document).ready(function () {
         load_datatable();
      });

      var filterTimeout;
      $('#filter_damage_type, #filter_status, #damage_from_date, #damage_to_date').on('change', function () {
         clearTimeout(filterTimeout);
         filterTimeout = setTimeout(function () {
            $('#damage_datatable').DataTable().destroy();
            load_datatable();
         }, 500);
      });

      function delete_damage(id) {
         if (confirm('Delete this damage record?')) {
            var base_url = $('#base_url').val().trim();
            $('.box').append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
            $.post(base_url + 'damage/delete_damage', { id: id }, function (result) {
               result = result.trim();
               if (result === 'success') {
                  toastr['success']('Record Deleted Successfully!');
                  $('#damage_datatable').DataTable().ajax.reload();
               } else {
                  toastr['error'](result || 'Failed to delete. Try again!');
               }
               $('.overlay').remove();
            });
         }
      }

      function approve_damage(id) {
         if (confirm('Approve this damage record? Stock will be deducted.')) {
            var base_url = $('#base_url').val().trim();
            $('.box').append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
            $.post(base_url + 'damage/approve', { id: id }, function (result) {
               result = result.trim();
               if (result === 'success') {
                  toastr['success']('Damage Approved! Stock updated.');
                  success.currentTime = 0;
                  success.play();
                  $('#damage_datatable').DataTable().ajax.reload();
               } else if (result === 'insufficient_stock') {
                  toastr['error']('Insufficient stock for one or more items!');
               } else {
                  toastr['error'](result || 'Failed to approve. Try again!');
               }
               $('.overlay').remove();
            });
         }
      }

      function multi_delete_damage() {
         if (confirm('Are you sure you want to delete selected records?')) {
            var data = new FormData($('#table_form')[0]);
            if (!xss_validation(data)) { return false; }
            $('.box').append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
            $.ajax({
               type: 'POST',
               url: 'damage/multi_delete',
               data: data,
               cache: false,
               contentType: false,
               processData: false,
               success: function (result) {
                  result = result.trim();
                  if (result === 'success') {
                     toastr['success']('Records Deleted Successfully!');
                     success.currentTime = 0;
                     success.play();
                     $('#damage_datatable').DataTable().ajax.reload();
                     $('.delete_btn').hide();
                     $('.group_check').prop('checked', false).iCheck('update');
                  } else {
                     toastr['error'](result || 'Failed to delete. Try again!');
                  }
                  $('.overlay').remove();
               }
            });
         }
      }

      // group checkbox toggle
      $(document).on('ifChecked ifUnchecked', '.group_check', function (e) {
         var checked = (e.type === 'ifChecked');
         $('.column_checkbox').iCheck(checked ? 'check' : 'uncheck');
         checked ? $('.delete_btn').show() : $('.delete_btn').hide();
      });
      $(document).on('ifChecked ifUnchecked', '.column_checkbox', function () {
         var any = $('.column_checkbox').filter(function () { return $(this).is(':checked'); }).length > 0;
         any ? $('.delete_btn').show() : $('.delete_btn').hide();
      });
   </script>

   <!-- Sidebar highlighter -->
   <script>
      $(".<?php echo basename(__FILE__, '.php'); ?>-active-li").addClass("active");
   </script>

</body>
</html>
