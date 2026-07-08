<!DOCTYPE html>
<html>

<head>
   <!-- FORM CSS CODE -->
   <?php include "comman/code_css_form.php"; ?>
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

      <?php include "sidebar.php"; ?>

      <?php
      if (!isset($damage_id) || empty($damage_id)) {
         $damage_date   = show_date(date("d-m-Y"));
         $warehouse_id  = '';
         $damage_type   = 'general';
         $reason        = '';
         $note          = '';
         $damage_code   = '';
         $save_operation = true;
      } else {
         $q2 = $this->db->query("SELECT * FROM db_damage WHERE id = " . (int)$damage_id);
         $dmg = $q2->row();
         $damage_date   = show_date($dmg->damage_date);
         $warehouse_id  = $dmg->warehouse_id;
         $damage_type   = $dmg->damage_type;
         $reason        = $dmg->reason;
         $note          = $dmg->note;
         $damage_code   = $dmg->damage_code;
         $items_count   = $this->db->query("SELECT COUNT(*) AS cnt FROM db_damageitems WHERE damage_id = " . (int)$damage_id)->row()->cnt;
         $save_operation = false;
      }
      ?>

      <!-- Content Wrapper -->
      <div class="content-wrapper">

         <!-- Content Header -->
         <section class="content-header">
            <h1>
               <?= $page_title; ?>
               <small><?= $save_operation ? 'New Damage Entry' : 'Edit Damage Entry'; ?></small>
            </h1>
            <ol class="breadcrumb">
               <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
               <li><a href="<?php echo $base_url; ?>damage">Damage List</a></li>
               <li><a href="<?php echo $base_url; ?>damage/add">New Damage</a></li>
               <li class="active"><?= $page_title; ?></li>
            </ol>
         </section>

         <!-- Main content -->
         <section class="content">
            <div class="row">
               <!-- Flash messages -->
               <?php include "comman/code_flashdata.php"; ?>

               <div class="col-md-12">
                  <div class="box box-info">

                     <?= form_open('#', array('class' => 'form-horizontal', 'id' => 'damage-form', 'enctype' => 'multipart/form-data', 'method' => 'POST')); ?>
                     <input type="hidden" id="base_url" value="<?php echo $base_url; ?>">
                     <input type="hidden" value="1"  id="hidden_rowcount"        name="hidden_rowcount">
                     <input type="hidden" value="0"  id="hidden_update_rowid"    name="hidden_update_rowid">
                     <input type="hidden" value=""   id="hidden_total_amt"       name="hidden_total_amt">
                     <input type="hidden" value="0"  id="hidden_total_qty"       name="hidden_total_qty">

                     <!-- ===== HEADER FIELDS ===== -->
                     <div class="box-body">

                        <!-- Row 1: Date + Warehouse -->
                        <div class="form-group">
                           <label for="damage_date" class="col-sm-2 control-label">
                              Damage Date <label class="text-danger">*</label>
                           </label>
                           <div class="col-sm-3">
                              <div class="input-group date">
                                 <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                 <input type="text" class="form-control pull-right datepicker"
                                        id="damage_date" name="damage_date" readonly
                                        value="<?= $damage_date; ?>">
                              </div>
                              <span id="damage_date_msg" style="display:none" class="text-danger"></span>
                           </div>

                           <label for="warehouse_id" class="col-sm-2 control-label">Warehouse</label>
                           <div class="col-sm-3">
                              <select class="form-control select2" id="warehouse_id" name="warehouse_id" style="width:100%;">
                                 <option value="">- Select -</option>
                                 <?php
                                 $wh = $this->db->query("SELECT id, warehouse_name FROM db_warehouse WHERE status=1 ORDER BY warehouse_name");
                                 foreach ($wh->result() as $w) {
                                    $sel = ($warehouse_id == $w->id) ? 'selected' : '';
                                    echo "<option value='{$w->id}' {$sel}>{$w->warehouse_name}</option>";
                                 }
                                 ?>
                              </select>
                           </div>
                        </div>

                        <!-- Row 2: Damage Type + Damage Code -->
                        <div class="form-group">
                           <label for="damage_type" class="col-sm-2 control-label">Damage Type</label>
                           <div class="col-sm-3">
                              <select class="form-control select2" id="damage_type" name="damage_type" style="width:100%;">
                                 <?php
                                 $types = ['general' => 'General', 'expired' => 'Expired', 'broken' => 'Broken', 'lost' => 'Lost', 'other' => 'Other'];
                                 foreach ($types as $val => $label) {
                                    $sel = ($damage_type == $val) ? 'selected' : '';
                                    echo "<option value='{$val}' {$sel}>{$label}</option>";
                                 }
                                 ?>
                              </select>
                           </div>

                           <label for="damage_code" class="col-sm-2 control-label">Damage Code</label>
                           <div class="col-sm-3">
                              <input type="text" class="form-control" id="damage_code" name="damage_code"
                                     placeholder="Auto-generated if blank"
                                     value="<?= htmlspecialchars($damage_code); ?>">
                           </div>
                        </div>

                        <!-- Row 3: Reason + Note -->
                        <div class="form-group">
                           <label for="reason" class="col-sm-2 control-label">Reason</label>
                           <div class="col-sm-3">
                              <input type="text" class="form-control" id="reason" name="reason"
                                     placeholder="Damage reason"
                                     value="<?= htmlspecialchars($reason); ?>">
                           </div>

                           <label for="damage_note" class="col-sm-2 control-label">Note</label>
                           <div class="col-sm-3">
                              <textarea class="form-control" id="damage_note" name="note"
                                        rows="1"><?= htmlspecialchars($note); ?></textarea>
                           </div>
                        </div>

                     </div>
                     <!-- /.box-body -->

                     <!-- ===== ITEM TABLE ===== -->
                     <div class="row">
                        <div class="col-md-12">
                           <div class="col-md-12">
                              <div class="box">
                                 <div class="box-info">
                                    <div class="box-header">
                                       <div class="col-md-8 col-md-offset-2 d-flex justify-content">
                                          <div class="input-group">
                                             <span class="input-group-addon" title="Search Items"><i class="fa fa-barcode"></i></span>
                                             <input type="text" class="form-control"
                                                    placeholder="Item name / Barcode / Item code"
                                                    id="item_search">
                                          </div>
                                       </div>
                                    </div>
                                    <div class="box-body">
                                       <div class="table-responsive" style="width:100%">
                                          <table class="table table-hover table-bordered" style="width:100%" id="damage_table">
                                             <thead class="custom_thead">
                                                <tr class="bg-primary">
                                                   <th style="width:25%">Item Name</th>
                                                   <th style="width:15%;min-width:180px;">Quantity</th>
                                                   <th style="width:15%">Unit Cost</th>
                                                   <th style="width:15%">Total Value</th>
                                                   <th style="width:20%">Item Reason</th>
                                                   <th style="width:10%">Action</th>
                                                </tr>
                                             </thead>
                                             <tbody>
                                                <!-- rows added dynamically -->
                                             </tbody>
                                          </table>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>

                        <!-- ===== BOTTOM: NOTE + TOTALS ===== -->
                        <div class="col-md-6">
                           <div class="row">
                              <div class="col-md-12">
                                 <div class="form-group">
                                    <label class="col-sm-4 control-label">Total Quantity</label>
                                    <div class="col-sm-4">
                                       <label class="control-label total_quantity text-success" style="font-size:15pt;">0</label>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="row">
                              <div class="col-md-12">
                                 <div class="form-group">
                                    <label for="damage_note_bottom" class="col-sm-4 control-label">Additional Note</label>
                                    <div class="col-sm-8">
                                       <textarea class="form-control text-left" id="damage_note_bottom"
                                                 name="note_extra" rows="3"
                                                 placeholder="Additional notes..."></textarea>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>

                        <div class="col-md-6">
                           <div class="row">
                              <div class="col-md-12">
                                 <div class="form-group">
                                    <table class="col-md-9">
                                       <tr>
                                          <th class="text-right" style="font-size:17px;">Total Qty</th>
                                          <th class="text-right" style="padding-left:10%;font-size:17px;">
                                             <h4><b id="summary_total_qty">0</b></h4>
                                          </th>
                                       </tr>
                                       <tr>
                                          <th class="text-right" style="font-size:17px;">Total Value</th>
                                          <th class="text-right" style="padding-left:10%;font-size:17px;">
                                             <h4><b id="summary_total_value">0.00</b></h4>
                                          </th>
                                       </tr>
                                    </table>
                                 </div>
                              </div>
                           </div>
                        </div>

                     </div>
                     <!-- /.row -->

                     <!-- ===== FOOTER BUTTONS ===== -->
                     <div class="box-footer col-sm-12">
                        <center>
                           <?php
                           if (!$save_operation) {
                              echo '<input type="hidden" name="damage_id" id="damage_id" value="' . (int)$damage_id . '"/>';
                              $btn_id   = 'update';
                              $btn_name = 'Update';
                           } else {
                              $btn_id   = 'save';
                              $btn_name = 'Save';
                           }
                           ?>
                           <div class="col-md-3 col-md-offset-3">
                              <button type="button" id="<?= $btn_id; ?>"
                                      class="btn bg-maroon btn-block btn-flat btn-lg"
                                      title="Save Data"><?= $btn_name; ?></button>
                           </div>
                           <div class="col-sm-3">
                              <a href="<?= base_url(); ?>damage">
                                 <button type="button" class="btn bg-gray btn-block btn-flat btn-lg"
                                         title="Back to list">Close</button>
                              </a>
                           </div>
                        </center>
                     </div>

                     <?= form_close(); ?>

                  </div>
               </div>

            </div>
         </section>
         <!-- /.content -->
      </div>
      <!-- /.content-wrapper -->

      <?php include "footer.php"; ?>
      <?php include "comman/code_js_sound.php"; ?>
      <?php include "comman/code_js_form.php"; ?>

      <div class="control-sidebar-bg"></div>
   </div>
   <!-- ./wrapper -->

   <script src="<?php echo $theme_link; ?>js/damage.js"></script>

   <script>
      // Pass save/update mode to damage.js
      function save_operation() {
         <?php if ($save_operation) { ?>
            return true;
         <?php } else { ?>
            return false;
         <?php } ?>
      }

      // Initialise Select2
      $(".select2").select2();

      // Datepicker
      $('.datepicker').datepicker({
         autoclose: true,
         format: 'dd-mm-yyyy',
         todayHighlight: true
      });

      <?php if (!$save_operation) { ?>
         // Load existing items for edit mode
         $(document).ready(function () {
            var base_url = '<?= base_url(); ?>';
            var damage_id = '<?= (int)$damage_id; ?>';
            $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
            $.post(base_url + "damage/return_damage_list/" + damage_id, {}, function (result) {
               $('#damage_table tbody').append(result);
               $("#hidden_rowcount").val(parseFloat(<?= (int)$items_count; ?>) + 1);
               success.currentTime = 0;
               success.play();
               damage_total();
               $(".overlay").remove();
            });
         });
      <?php } ?>
   </script>

   <!-- Sidebar highlighter -->
   <script>
      $(".<?php echo basename(__FILE__, '.php'); ?>-active-li").addClass("active");
   </script>

</body>
</html>
