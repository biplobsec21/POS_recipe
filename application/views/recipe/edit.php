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
    .table>thead>tr>td {
      padding-left: 2px;
      padding-right: 2px;
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
          <?= isset($page_title) ? $page_title : ''; ?>
          <small><?= $this->lang->line('edit_recipe'); ?></small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> <?= $this->lang->line('home'); ?></a></li>
          <li><a href="<?= base_url('recipe'); ?>"><?= $this->lang->line('recipes_list'); ?></a></li>
          <li class="active"><?= isset($page_title) ? $page_title : ''; ?></li>
        </ol>
      </section>

      <!-- Main content -->
      <section class="content">
        <div class="row">
          <!-- ALERT MESSAGE -->
          <?php $this->load->view('comman/code_flashdata'); ?>

          <div class="col-md-12">
            <div class="box box-info ">
              <div class="box-header with-border">
                <h3 class="box-title"><?= $this->lang->line('please_enter_valid_data'); ?></h3>
              </div>

              <?= form_open('#', ['class' => 'form-horizontal', 'id' => 'recipe-form', 'enctype' => 'multipart/form-data', 'method' => 'POST']); ?>
              <input type="hidden" id="base_url" value="<?= isset($base_url) ? $base_url : base_url(); ?>">
              <input type="hidden" name="recipe_id" id="recipe_id" value="<?= isset($recipe->id) ? $recipe->id : ''; ?>">
              <input type="hidden" value="1" id="hidden_rowcount" name="hidden_rowcount">

              <div class="box-body">
                <div class="form-group">
                  <label for="recipe_name" class="col-sm-2 control-label"><?= $this->lang->line('recipe_name'); ?><label class="text-danger">*</label></label>
                  <div class="col-sm-4">
                    <input type="text" class="form-control" id="recipe_name" name="recipe_name" value="<?= isset($recipe->recipe_name) ? $recipe->recipe_name : ''; ?>" autofocus>
                    <span id="recipe_name_msg" style="display:none" class="text-danger"></span>
                  </div>
                </div>

                <div class="form-group">
                  <label for="output_product_id" class="col-sm-2 control-label"><?= $this->lang->line('output_product'); ?><label class="text-danger">*</label></label>
                  <div class="col-sm-4">
                    <select class="form-control select2" id="output_product_id" name="output_product_id" style="width:100%;">
                      <option value="">-Select-</option>
                      <?php if (!empty($all_items)): foreach ($all_items as $item): ?>
                          <?php $selected = (isset($recipe->output_product_id) && $item->id == $recipe->output_product_id) ? 'selected' : ''; ?>
                          <option value="<?= $item->id; ?>" <?= $selected; ?>><?= htmlspecialchars($item->item_name); ?></option>
                      <?php endforeach;
                      endif; ?>
                    </select>
                    <span id="output_product_id_msg" style="display:none" class="text-danger"></span>
                  </div>
                </div>

                <div class="form-group">
                  <label for="yield_quantity" class="col-sm-2 control-label"><?= $this->lang->line('yield_quantity'); ?><label class="text-danger">*</label></label>
                  <div class="col-sm-4">
                    <input type="text" class="form-control only_currency" id="yield_quantity" name="yield_quantity" value="<?= isset($recipe->yield_quantity) ? $recipe->yield_quantity : ''; ?>">
                    <span id="yield_quantity_msg" style="display:none" class="text-danger"></span>
                  </div>
                </div>

                <div class="form-group">
                  <label for="notes" class="col-sm-2 control-label"><?= $this->lang->line('notes'); ?></label>
                  <div class="col-sm-4">
                    <textarea class="form-control" id="notes" name="notes"><?= isset($recipe->notes) ? $recipe->notes : ''; ?></textarea>
                    <span id="notes_msg" style="display:none" class="text-danger"></span>
                  </div>
                </div>

                <div class="col-md-12">
                  <div class="box">
                    <div class="box-info">
                      <div class="box-header">
                        <div class="col-md-8 col-md-offset-2">
                          <div class="input-group">
                            <span class="input-group-addon" title="<?= $this->lang->line('select_ingredients'); ?>"><i class="fa fa-barcode"></i></span>
                            <input type="text" class="form-control" placeholder="<?= $this->lang->line('item_name_or_code'); ?>" id="item_search">
                          </div>
                        </div>
                      </div>

                      <div class="box-body">
                        <div class="table-responsive" style="width:100%">
                          <table class="table table-hover table-bordered" id="recipe_items_table" style="width:100%">
                            <thead class="custom_thead">
                              <tr class="bg-primary">
                                <th style="width:30%"><?= $this->lang->line('item_name'); ?></th>
                                <th style="width:20%"><?= $this->lang->line('quantity'); ?></th>
                                <th style="width:20%"><?= $this->lang->line('unit'); ?></th>
                                <th style="width:20%"><?= $this->lang->line('cost_per_unit'); ?></th>
                                <th style="width:10%"><?= $this->lang->line('action'); ?></th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php
                              $i = 1;
                              if (!empty($recipe_items)):
                                foreach ($recipe_items as $item):
                                  $item_details = $this->Items_model->get_by_id($item->item_id);
                                  if ($item_details):
                              ?>
                                    <tr id="row_<?= $i; ?>">
                                      <td>
                                        <input type="hidden" name="item_id[]" value="<?= $item->item_id; ?>">
                                        <?= htmlspecialchars($item_details->item_name); ?>
                                        <?php if (isset($item_details->stock) && $item_details->stock <= 0): ?>
                                          <span class="label label-danger">Out of Stock</span>
                                        <?php endif; ?>
                                      </td>
                                      <td>
                                        <input type="text" class="form-control only_currency item_qty" name="quantity[]" value="<?= $item->quantity; ?>" data-stock="<?= isset($item_details->stock) ? $item_details->stock : 0; ?>" onchange="checkStock(this)">
                                      </td>
                                      <td>
                                        <input type="text" class="form-control" name="unit[]" value="<?= isset($item_details->unit_name) ? $item_details->unit_name : ''; ?>" readonly>
                                      </td>
                                      <td>
                                        <input type="text" class="form-control only_currency" name="cost_per_unit[]" value="<?= isset($item_details->purchase_price) ? $item_details->purchase_price : '0.00'; ?>" readonly>
                                      </td>
                                      <td>
                                        <button type="button" class="btn btn-danger" onclick="remove_recipe_item(<?= $i; ?>)"><i class="fa fa-trash"></i></button>
                                      </td>
                                    </tr>
                              <?php
                                  endif;
                                  $i++;
                                endforeach;
                              endif;
                              ?>
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
                    <button type="button" id="update" class="btn btn-block btn-success" title="<?= $this->lang->line('update_data'); ?>">Update</button>
                  </div>
                  <div class="col-sm-3">
                    <a href="<?= base_url('recipe'); ?>">
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
          <!-- /.col (right) -->
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

  <script>
    var base_url = "<?= isset($base_url) ? $base_url : base_url(); ?>";

    // Autocomplete for item search using recipe controller endpoint
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
              item.label = (item.value || item.item_name) + ' (' + (item.item_code || '') + ')' + (item.has_stock === false ? ' [Out of Stock]' : '');
              item.value = item.value || item.item_name;
              return item;
            }));
          },
          error: function(xhr) {
            console.error('autocomplete error', xhr.responseText);
            response([]);
          }
        });
      },
      minLength: 1,
      select: function(event, ui) {
        if (ui.item.has_stock === false) {
          toastr.warning('Warning: This item is out of stock!');
          return false;
        }

        add_recipe_item(
          ui.item.id || ui.item.item_id,
          ui.item.value,
          ui.item.unit_name || '',
          ui.item.purchase_price || 0,
          ui.item.stock || 0
        );
        $(this).val('');
        return false;
      }
    });

    function add_recipe_item(item_id, item_name, unit_name, purchase_price, stock) {
      // prevent duplicates
      var exists = false;
      $('input[name="item_id[]"]').each(function() {
        if ($(this).val() == item_id) {
          exists = true;
          return false;
        }
      });
      if (exists) {
        toastr.warning('Item already added');
        return;
      }

      var rowcount = parseInt($("#hidden_rowcount").val() || 1);
      var new_row = `
      <tr id="row_${rowcount}">
        <td>
          <input type="hidden" name="item_id[]" value="${item_id}">
          ${item_name}
        </td>
        <td>
          <input type="text" class="form-control only_currency item_qty" name="quantity[]" value="1.00" data-stock="${stock || 0}" onchange="checkStock(this)">
        </td>
        <td>
          <input type="text" class="form-control" name="unit[]" value="${unit_name}" readonly>
        </td>
        <td>
          <input type="text" class="form-control only_currency" name="cost_per_unit[]" value="${purchase_price}" readonly>
        </td>
        <td>
          <button type="button" class="btn btn-danger" onclick="remove_recipe_item(${rowcount})"><i class="fa fa-trash"></i></button>
        </td>
      </tr>
      `;
      $("#recipe_items_table tbody").append(new_row);
      $("#hidden_rowcount").val(rowcount + 1);
    }

    function remove_recipe_item(row_id) {
      $("#row_" + row_id).remove();
    }

    function checkStock(input) {
      var stock = parseFloat($(input).data('stock') || 0);
      var qty = parseFloat($(input).val() || 0);
      if (qty > stock && stock >= 0) {
        toastr.warning('Warning: Quantity exceeds available stock!');
        $(input).val(stock.toFixed(2));
      }
    }

    $(document).ready(function() {
      $("#hidden_rowcount").val($("#recipe_items_table tbody tr").length + 1);
    });

    // Make sidebar menu hughlighter/selector
    $(".recipe-active-li").addClass("active");
  </script>

  <script src="<?php echo isset($theme_link) ? $theme_link : base_url('theme/'); ?>js/recipe.js"></script>
</body>

</html>