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
          <small><?= $this->lang->line('add_new_recipe'); ?></small>
        </h1>

        <ol class="breadcrumb">
          <li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="<?php echo $base_url; ?>recipe"><?= $this->lang->line('recipes_list'); ?></a></li>
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
                <h3 class="box-title"><?= $this->lang->line('please_enter_valid_data'); ?></h3>
              </div>
              <!-- /.box-header -->
              <!-- form start -->
              <?= form_open('#', array('class' => 'form-horizontal', 'id' => 'recipe-form', 'enctype' => 'multipart/form-data', 'method' => 'POST')); ?>
              <input type="hidden" id="base_url" value="<?php echo isset($base_url) ? $base_url : base_url(); ?>">
              <input type="hidden" value='1' id="hidden_rowcount" name="hidden_rowcount">

              <div class="box-body">
                <div class="form-group">
                  <label for="recipe_name" class="col-sm-2 control-label"><?= $this->lang->line('recipe_name'); ?><label class="text-danger">*</label></label>
                  <div class="col-sm-4">
                    <input type="text" class="form-control" id="recipe_name" name="recipe_name" placeholder="" value="" autofocus>
                    <span id="recipe_name_msg" style="display:none" class="text-danger"></span>
                  </div>
                </div>

                <div class="form-group">
                  <label for="output_product_id" class="col-sm-2 control-label"><?= $this->lang->line('output_product'); ?><label class="text-danger">*</label></label>
                  <div class="col-sm-4">
                    <select class="form-control select2" id="output_product_id" name="output_product_id" style="width: 100%;">
                      <option value="">-Select-</option>
                      <?php foreach ($all_items as $item): ?>
                        <option value="<?= $item->id; ?>"><?= $item->item_name; ?></option>
                      <?php endforeach; ?>
                    </select>
                    <span id="output_product_id_msg" style="display:none" class="text-danger"></span>
                  </div>
                </div>

                <div class="form-group">
                  <label for="yield_quantity" class="col-sm-2 control-label"><?= $this->lang->line('yield_quantity'); ?><label class="text-danger">*</label></label>
                  <div class="col-sm-4">
                    <input type="text" class="form-control only_currency" id="yield_quantity" name="yield_quantity" placeholder="" value="">
                    <span id="yield_quantity_msg" style="display:none" class="text-danger"></span>
                  </div>
                </div>

                <div class="form-group">
                  <label for="notes" class="col-sm-2 control-label"><?= $this->lang->line('notes'); ?></label>
                  <div class="col-sm-4">
                    <textarea class="form-control" id="notes" name="notes" placeholder=""></textarea>
                    <span id="notes_msg" style="display:none" class="text-danger"></span>
                  </div>
                </div>

                <div class="col-md-12">
                  <div class="box">
                    <div class="box-info">
                      <div class="box-header">
                        <div class="col-md-8 col-md-offset-2 d-flex justify-content">
                          <div class="input-group">
                            <span class="input-group-addon" title="<?= $this->lang->line('select_ingredients'); ?>"><i class="fa fa-barcode"></i></span>
                            <input type="text" class="form-control" placeholder="<?= $this->lang->line('item_name_or_code'); ?>" id="item_search">
                          </div>
                        </div>
                      </div>
                      <div class="box-body">
                        <div class="table-responsive" style="width: 100%">
                          <table class="table table-hover table-bordered" style="width:100%" id="recipe_items_table">
                            <thead class="custom_thead">
                              <tr class="bg-primary">
                                <th rowspan='2' style="width:30%"><?= $this->lang->line('item_name'); ?></th>
                                <th rowspan='2' style="width:20%"><?= $this->lang->line('quantity'); ?></th>
                                <th rowspan='2' style="width:20%"><?= $this->lang->line('unit'); ?></th>
                                <th rowspan='2' style="width:20%"><?= $this->lang->line('cost_per_unit'); ?></th>
                                <th rowspan='2' style="width:10%"><?= $this->lang->line('action'); ?></th>
                              </tr>
                            </thead>
                            <tbody>
                              <!-- Recipe items will be added here dynamically -->
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
                    <button type="button" id="save" class="btn btn-block btn-success" title="<?= $this->lang->line('save_data'); ?>"><?= $this->lang->line('save'); ?></button>
                  </div>
                  <div class="col-sm-3">
                    <a href="<?= base_url('dashboard'); ?>">
                      <button type="button" class="col-sm-3 btn btn-block btn-warning close_btn" title="<?= $this->lang->line('go_to_dashboard'); ?>"><?= $this->lang->line('close'); ?></button>
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
  <!-- TABLES CODE -->
  <?php $this->load->view('comman/code_js_form'); ?>

  <script src="<?php echo isset($theme_link) ? $theme_link : base_url('theme/'); ?>js/recipe.js"></script>
  <script>
    var base_url = "<?= base_url(); ?>";

    // Item Search Autocomplete
    $("#item_search").autocomplete({
      source: function(request, response) {
        $.ajax({
          url: base_url + 'recipe/get_json_items_details',
          method: 'GET',
          dataType: 'json',
          data: {
            name: request.term,
          },
          success: function(data) {
            response($.map(data, function(item) {
              // Check if item has stock
              if (!item.has_stock) {
                // You can choose to either not show items without stock
                // or show them with a warning
                item.label += ' (Out of Stock)';
              }
              return item;
            }));
          }
        });
      },
      minLength: 1,
      select: function(event, ui) {
        if (!ui.item.has_stock) {
          toastr.warning('Warning: This item is out of stock!');
          return false;
        }

        add_recipe_item(
          ui.item.id,
          ui.item.value,
          ui.item.unit_name,
          ui.item.purchase_price
        );
        $(this).val('');
        return false;
      }
    });

    function add_recipe_item(item_id, item_name, unit_name, purchase_price) {
      var rowcount = $("#hidden_rowcount").val();
      var new_row = `
    <tr id="row_${rowcount}">
        <td>
            <input type="hidden" name="item_id[]" value="${item_id}">
            ${item_name}
        </td>
        <td>
            <input type="text" class="form-control only_currency" name="quantity[]" value="1.00">
        </td>
        <td>
            <input type="text" class="form-control" name="unit[]" value="${unit_name}" readonly>
        </td>
        <td>
            <input type="text" class="form-control only_currency" name="cost_per_unit[]" value="${purchase_price}" readonly>
        </td>
        <td>
            <button type="button" class="btn btn-danger" onclick="remove_recipe_item(${rowcount})">
                <i class="fa fa-trash"></i>
            </button>
        </td>
    </tr>`;

      $("#recipe_items_table tbody").append(new_row);
      $("#hidden_rowcount").val(parseInt(rowcount) + 1);
    }

    function remove_recipe_item(row_id) {
      $("#row_" + row_id).remove();
    }

    // Make sidebar menu hughlighter/selector
    $(".recipe-active-li").addClass("active");
  </script>
</body>

</html>