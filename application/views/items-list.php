<!DOCTYPE html>
<html>

<head>
<!-- TABLES CSS CODE -->
<?php include"comman/code_css_datatable.php"; ?>

<!-- Lightbox -->
<link rel="stylesheet" href="<?php echo $theme_link; ?>plugins/lightbox/ekko-lightbox.css">
</head>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <!-- Left side column. contains the logo and sidebar -->
  
  <?php include"sidebar.php"; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <?=$page_title;?>
        <small>View/Search Items</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active"><?=$page_title;?></li>
      </ol>
    </section>

    <!-- Main content -->
    <?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
    <input type="hidden" id='base_url' value="<?=$base_url;?>">

    <section class="content">
      <div class="row">
        <!-- ********** ALERT MESSAGE START******* -->
        <?php include"comman/code_flashdata.php"; ?>
        <!-- ********** ALERT MESSAGE END******* -->
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <div class="row">
                    <div class="col-md-12">
                    <div class="col-md-3">
                        <label for="brand_id" class=" control-label"><?= $this->lang->line('brand'); ?></label>
                          <select class="form-control select2" id="brand_id" name="brand_id"  style="width: 100%;">
                            <?php
                               $query1="select * from db_brands where status=1";
                               $q1=$this->db->query($query1);
                               if($q1->num_rows($q1)>0)
                                {  echo '<option value="">-Select-</option>'; 
                                    foreach($q1->result() as $res1)
                                  { 
                                    echo "<option value='".$res1->id."'>".$res1->brand_name."</option>";
                                  }
                                }
                                else
                                {
                                   ?>
                            <option value="">No Records Found</option>
                            <?php
                               }
                               ?>
                         </select>
                    </div>
                    <div class="col-md-3">
                        <label for="category_id" class=" control-label"><?= $this->lang->line('category'); ?></label>
                          <select class="form-control select2" id="category_id" name="category_id"  style="width: 100%;">
                            <?php
                               $query1="select * from db_category where status=1";
                               $q1=$this->db->query($query1);
                               if($q1->num_rows($q1)>0)
                                {  echo '<option value="">-Select-</option>'; 
                                    foreach($q1->result() as $res1)
                                  { 
                                    echo "<option value='".$res1->id."'>".$res1->category_name."</option>";
                                  }
                                }
                                else
                                {
                                   ?>
                            <option value="">No Records Found</option>
                            <?php
                               }
                               ?>
                         </select>
                    </div>
                    
                  </div>
                </div>

              <?php if($CI->permissions('items_add')) { ?>
              <div class="box-tools">
                <a class="btn btn-block btn-info " href="<?php echo $base_url; ?>items/add">
                <i class="fa fa-plus " ></i> <?= $this->lang->line('new_item'); ?></a>
              </div>
             <?php } ?>
             
             <?php if($CI->permissions('items_edit')) { ?>
              <div class="box-tools" style="margin-top: 5px;display:none !important">
                <button type="button" class="btn btn-block btn-warning" id="sync_all_items_btn">
                <i class="fa fa-refresh" ></i> Synchronize All Items Stock</button>
              </div>
             <?php } ?>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example2" class="table table-bordered table-striped" width="100%">
                <thead class="bg-primary ">
                <tr>
                  <th class="text-center">
                    <input type="checkbox" class="group_check checkbox" >
                  </th>
                  <th><?= $this->lang->line('image'); ?></th>
                  <th><?= $this->lang->line('item_code'); ?></th>
                  <th><?= $this->lang->line('item_name'); ?></th>
                  <th><?= $this->lang->line('brand'); ?></th>
                  <th><?= $this->lang->line('category'); ?></th>
                  <th><?= $this->lang->line('unit'); ?></th>
                  <th><?= $this->lang->line('stock_qty'); ?></th>
                  <th><?= $this->lang->line('minimum_qty'); ?></th>
                  <?php 
                   // if($CI->permissions('don_not_show_purchase_unit_price_view') && $this->session->userdata('inv_userid') != 1){

                  //  }else{ ?>
                        <!-- <th><?= $this->lang->line('purchase_price'); ?></th> -->

                   <?php //} ?>
                  <th><?= $this->lang->line('purchase_price'); ?></th>
                  <th><?= $this->lang->line('final_sales_price'); ?></th>
                  <th><?= $this->lang->line('tax'); ?></th>
	         	  	  <th><?= $this->lang->line('status'); ?></th>
                  <th><?= $this->lang->line('action'); ?></th>
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
     <?= form_close();?>
  </div>
  <!-- /.content-wrapper -->
  <?php include"footer.php"; ?>
  <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->

<!-- SOUND CODE -->
<?php include"comman/code_js_sound.php"; ?>
<!-- TABLES CODE -->
<?php include"comman/code_js_datatable.php"; ?>
<!-- Lightbox -->
<script src="<?php echo $theme_link; ?>plugins/lightbox/ekko-lightbox.js"></script>
<script type="text/javascript">
  $(document).on('click', '[data-toggle="lightbox"]', function(event) {
            event.preventDefault();
            $(this).ekkoLightbox();
        });
</script>
<script type="text/javascript">
function load_datatable(){
    //datatables
   var table = $('#example2').DataTable({ 

      /* FOR EXPORT BUTTONS START*/
  dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>tip',
 /* dom:'<"row"<"col-sm-12"<"pull-left"B><"pull-right">>> <"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr>>>tip',*/
      buttons: {
        buttons: [
            {
                className: 'btn bg-red color-palette btn-flat hidden delete_btn pull-left',
                text: 'Delete',
                action: function ( e, dt, node, config ) {
                    multi_delete();
                }
            },
            { extend: 'copy', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [2,3,4,5,6,7,8,9,10,11,12]} },
            { extend: 'excel', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [2,3,4,5,6,7,8,9,10,11,12]} },
            { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [2,3,4,5,6,7,8,9,10,11,12]} },
            { extend: 'print', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [2,3,4,5,6,7,8,9,10,11,12]} },
            { extend: 'csv', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [2,3,4,5,6,7,8,9,10,11,12]} },
            { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat',text:'Columns' },  

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
            "url": "<?php echo site_url('items/ajax_list')?>",
            "type": "POST",
            "data":{
              brand_id : $("#brand_id").val(),
              category_id : $("#category_id").val(),
            },
            
            complete: function (data) {
             $('.column_checkbox').iCheck({
                checkboxClass: 'icheckbox_square-orange',
                /*uncheckedClass: 'bg-white',*/
                radioClass: 'iradio_square-orange',
                increaseArea: '10%' // optional
              });
             call_code();
              //$(".delete_btn").hide();
             },

        },

        //Set column definition initialisation properties.
        "columnDefs": [
        { 
            "targets": [ 0,1,12,13 ], //first column / numbering column
            "orderable": false, //set not orderable
        },
        {
            "targets" :[0],
            "className": "text-center",
        },
        
        ],
    });
    new $.fn.dataTable.FixedHeader( table );
}

$(document).ready(function() {
    //datatables
   load_datatable();
});
$("#brand_id,#category_id").on("change",function(){
    $('#example2').DataTable().destroy();
    load_datatable();
});

// Synchronize all items stock
$('#sync_all_items_btn').on('click', function() {
    var confirmSync = confirm('This will synchronize stock quantity for ALL items to match the ledger. Continue?');
    if (!confirmSync) {
        return;
    }
    
    var btn = $(this);
    var originalText = btn.html();
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Synchronizing...');
    
    $.ajax({
        url: '<?php echo site_url("items/sync_all_items_stock") ?>',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Show detailed log
                var logMessage = response.message + '\n\n';
                
                if (response.synced_items && response.synced_items.length > 0) {
                    logMessage += '=== SYNCED ITEMS ===\n';
                    $.each(response.synced_items, function(index, item) {
                        var sign = item.difference >= 0 ? '+' : '';
                        logMessage += '\n' + (index + 1) + '. ' + item.item_code + ' - ' + item.item_name + '\n';
                        logMessage += '   Old Stock: ' + parseFloat(item.old_stock).toFixed(4) + '\n';
                        logMessage += '   New Stock: ' + parseFloat(item.new_stock).toFixed(4) + '\n';
                        logMessage += '   Difference: ' + sign + parseFloat(item.difference).toFixed(4) + '\n';
                    });
                }
                
                if (response.error_items && response.error_items.length > 0) {
                    logMessage += '\n\n=== FAILED ITEMS ===\n';
                    $.each(response.error_items, function(index, item) {
                        logMessage += '\n' + (index + 1) + '. ' + item.item_code + ' - ' + item.item_name + '\n';
                        logMessage += '   Error: ' + item.error + '\n';
                    });
                }
                
                // Create modal to show detailed log
                var modalHtml = '<div class="modal fade" id="syncLogModal">' +
                    '<div class="modal-dialog modal-lg">' +
                    '<div class="modal-content">' +
                    '<div class="modal-header">' +
                    '<button type="button" class="close" data-dismiss="modal">&times;</button>' +
                    '<h4 class="modal-title">Stock Synchronization Report</h4>' +
                    '</div>' +
                    '<div class="modal-body">' +
                    '<pre style="max-height: 500px; overflow-y: auto; background-color: #f5f5f5; padding: 15px; border-radius: 4px;">' + 
                    logMessage + 
                    '</pre>' +
                    '</div>' +
                    '<div class="modal-footer">' +
                    '<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>' +
                    '<button type="button" class="btn btn-info" onclick="downloadSyncLog()">Download Report</button>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>';
                
                // Remove old modal if exists
                $('#syncLogModal').remove();
                $('body').append(modalHtml);
                
                // Store log data for download
                window.syncLogData = {
                    message: response.message,
                    syncedItems: response.synced_items,
                    errorItems: response.error_items,
                    timestamp: new Date().toLocaleString()
                };
                
                $('#syncLogModal').modal('show');
                
                // Reload the datatable after a short delay
                setTimeout(function() {
                    $('#example2').DataTable().destroy();
                    load_datatable();
                }, 1500);
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('Failed to synchronize items stock. Please try again.');
        },
        complete: function() {
            btn.prop('disabled', false).html(originalText);
        }
    });
});

// Download sync log as CSV
function downloadSyncLog() {
    if (!window.syncLogData) return;
    
    var csv = 'Stock Synchronization Report\n';
    csv += 'Generated: ' + window.syncLogData.timestamp + '\n\n';
    csv += window.syncLogData.message + '\n\n';
    
    if (window.syncLogData.syncedItems && window.syncLogData.syncedItems.length > 0) {
        csv += 'SYNCED ITEMS\n';
        csv += 'Item Code,Item Name,Old Stock,New Stock,Difference\n';
        $.each(window.syncLogData.syncedItems, function(index, item) {
            csv += '"' + item.item_code + '","' + item.item_name + '",' + 
                   item.old_stock + ',' + item.new_stock + ',' + item.difference + '\n';
        });
    }
    
    if (window.syncLogData.errorItems && window.syncLogData.errorItems.length > 0) {
        csv += '\n\nFAILED ITEMS\n';
        csv += 'Item Code,Item Name,Error\n';
        $.each(window.syncLogData.errorItems, function(index, item) {
            csv += '"' + item.item_code + '","' + item.item_name + '","' + item.error + '"\n';
        });
    }
    
    // Download CSV
    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    var link = document.createElement('a');
    var url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'stock_sync_report_' + new Date().getTime() + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}


</script>


<script src="<?php echo $theme_link; ?>js/items.js"></script>

<!-- Make sidebar menu hughlighter/selector -->
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
		
</body>
</html>
