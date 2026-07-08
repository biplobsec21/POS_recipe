/**
 * damage.js
 * Mirrors sales.js exactly — autocomplete search, +/- qty, recalc, save/update AJAX.
 * Field naming convention matches Damage_model::return_row_with_data:
 *   tr_item_id_N          — hidden item id
 *   tr_available_qty_N    — hidden max stock
 *   td_data_N_3           — damage qty
 *   td_data_N_10          — unit cost
 *   td_data_N_9           — total value  (readonly)
 *   td_data_N_reason      — per-row reason
 */

/* ------------------------------------------------------------------ */
/* CURSOR HELPER                                                        */
/* ------------------------------------------------------------------ */
function damage_shift_cursor(kevent, target) {
  if (kevent.keyCode === 13) {
    $('#' + target).focus();
  }
}

/* ------------------------------------------------------------------ */
/* SAVE / UPDATE BUTTON                                                 */
/* ------------------------------------------------------------------ */
$('#save, #update').on('click', function (e) {
  var this_id = this.id;
  var base_url = $('#base_url').val().trim();
  var flag = true;

  function check_field(id) {
    if (!$('#' + id).val().trim()) {
      $('#' + id + '_msg').fadeIn(200).show().html('Required Field').addClass('required');
      flag = false;
    } else {
      $('#' + id + '_msg').fadeOut(200).hide();
    }
  }

  // Required header fields
  check_field('damage_date');

  if (!flag) {
    toastr['error']('Please fill all required (*) fields.');
    return;
  }

  // At least one item row must exist
  var rowcount = parseInt($('#hidden_rowcount').val(), 10);
  var has_items = false;
  for (var n = 1; n <= rowcount; n++) {
    if ($('#td_data_' + n + '_3').val() !== null && $('#td_data_' + n + '_3').val() !== '') {
      has_items = true;
      break;
    }
  }
  if (!has_items) {
    toastr['warning']('Please add at least one item!');
    $('#item_search').focus();
    return;
  }

  // Collect summary totals from the UI
  var total_qty = $('.total_quantity').text();
  var total_value = $('#summary_total_value').text();

  e.preventDefault();
  var data = new FormData($('#damage-form')[0]);

  if (typeof xss_validation === 'function' && !xss_validation(data)) {
    return false;
  }

  $('.box').append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
  $('#' + this_id).attr('disabled', true);

  $.ajax({
    type: 'POST',
    url: base_url + 'damage/damage_save_and_update'
      + '?command=' + this_id
      + '&rowcount=' + rowcount
      + '&total_qty=' + total_qty
      + '&total_value=' + total_value,
    data: data,
    cache: false,
    contentType: false,
    processData: false,
    success: function (result) {
      result = result.split('<<<###>>>');
      if (result[0] === 'success') {
        location.href = base_url + 'damage';
      } else if (result[0] === 'failed') {
        toastr['error']('Failed to save record. Please try again.');
        failed.currentTime = 0;
        failed.play();
      } else if (result[0] === 'approved_record') {
        toastr['error']('Approved records cannot be edited.');
      } else {
        alert(result[0]);
      }
      $('#' + this_id).attr('disabled', false);
      $('.overlay').remove();
    }
  });
});

/* ------------------------------------------------------------------ */
/* ITEM AUTOCOMPLETE SEARCH  (mirrors sales.js)                        */
/* ------------------------------------------------------------------ */
var damageAutocompleteTimeout;

$('#item_search').keypress(function (e) {
  if (e.which === 13) {
    $('#item_search').autocomplete('search');
  }
});

$('#item_search').bind('paste', function () {
  clearTimeout(damageAutocompleteTimeout);
  damageAutocompleteTimeout = setTimeout(function () {
    $('#item_search').autocomplete('search');
  }, 100);
});

$('#item_search').autocomplete({
  source: function (request, cb) {
    $.ajax({
      autoFocus: true,
      url: $('#base_url').val() + 'items/get_json_items_details',
      method: 'GET',
      dataType: 'json',
      data: { name: request.term },
      success: function (res) {
        var result = [{ label: 'No Records Found', value: '' }];
        if (res.length) {
          result = $.map(res, function (el) {
            return {
              label: el.item_code + ' -- [Qty: ' + el.stock + '] -- ' + el.label,
              value: '',
              id: el.id,
              item_name: el.value,
              stock: el.stock
            };
          });
        }
        cb(result);
      }
    });
  },
  response: function (e, ui) {
    if (ui.content.length === 1) {
      $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
      $(this).autocomplete('close');
    }
  },
  select: function (e, ui) {
    var stock, item_id;

    if (typeof ui.content !== 'undefined') {
      if (isNaN(ui.content[0].id)) { return; }
      stock = ui.content[0].stock;
      item_id = ui.content[0].id;
    } else {
      stock = ui.item.stock;
      item_id = ui.item.id;
    }

    if (parseFloat(stock) <= 0) {
      toastr['warning'](stock + ' items in stock — cannot add!');
      failed.currentTime = 0;
      failed.play();
      return false;
    }

    if (damage_restrict_quantity(item_id)) {
      damage_return_row_with_data(item_id);
    }
    $('#item_search').val('');
  }
});

/* ------------------------------------------------------------------ */
/* FETCH AND APPEND ONE ITEM ROW                                       */
/* ------------------------------------------------------------------ */
function damage_return_row_with_data(item_id) {
  $('#item_search').addClass('ui-autocomplete-loader-center');
  var base_url = $('#base_url').val().trim();
  var rowcount = $('#hidden_rowcount').val();

  $.post(
    base_url + 'damage/return_row_with_data/' + rowcount + '/' + item_id,
    {},
    function (result) {
      $('#damage_table tbody').append(result);
      $('#hidden_rowcount').val(parseFloat(rowcount) + 1);
      success.currentTime = 0;
      success.play();
      damage_total();
      $('#item_search').removeClass('ui-autocomplete-loader-center');
    }
  );
}

/* ------------------------------------------------------------------ */
/* QUANTITY CONTROLS                                                    */
/* ------------------------------------------------------------------ */
function damage_increment_qty(rowcount) {
  var item_id = $('#tr_item_id_' + rowcount).val().trim();
  if (!damage_restrict_quantity(item_id)) { return false; }

  var qty = parseFloat($('#td_data_' + rowcount + '_3').val());
  var avail_qty = parseFloat($('#tr_available_qty_' + rowcount).val());

  if (qty < avail_qty) {
    var new_qty = Math.min(qty + 1, avail_qty);
    $('#td_data_' + rowcount + '_3').val(new_qty);
  }
  damage_recalc(rowcount);
}

function damage_decrement_qty(rowcount) {
  var qty = parseFloat($('#td_data_' + rowcount + '_3').val());

  if (qty <= 1) {
    $('#td_data_' + rowcount + '_3').val(1);
    toastr['warning']('Minimum quantity is 1!');
    return;
  }
  $('#td_data_' + rowcount + '_3').val((qty - 1).toFixed(2));
  damage_recalc(rowcount);
}

function damage_qty_input(rowcount) {
  var qty = parseFloat($('#td_data_' + rowcount + '_3').val());
  var avail_qty = parseFloat($('#tr_available_qty_' + rowcount).val());

  if (qty > avail_qty) {
    $('#td_data_' + rowcount + '_3').val(avail_qty);
    toastr['warning']('Only ' + avail_qty + ' items in stock!');
  }
  damage_recalc(rowcount);
}

/* ------------------------------------------------------------------ */
/* RECALCULATE ONE ROW  (qty × unit_cost → total_value)                */
/* ------------------------------------------------------------------ */
function damage_recalc(rowcount) {
  var qty = parseFloat($('#td_data_' + rowcount + '_3').val()) || 0;
  var unit_cost = parseFloat($('#td_data_' + rowcount + '_10').val()) || 0;
  var line_total = qty * unit_cost;

  $('#td_data_' + rowcount + '_9').val(line_total.toFixed(2));
  damage_total();
}

/* ------------------------------------------------------------------ */
/* RECALCULATE GRAND TOTALS                                            */
/* ------------------------------------------------------------------ */
function damage_total() {
  var rowcount = parseInt($('#hidden_rowcount').val(), 10);
  var total_qty = 0;
  var total_value = 0;

  for (var i = 1; i <= rowcount; i++) {
    if (document.getElementById('td_data_' + i + '_3')) {
      var qty_val = $('#td_data_' + i + '_3').val();
      var tot_val = $('#td_data_' + i + '_9').val();

      if (qty_val !== null && qty_val !== '') {
        total_qty += parseFloat(qty_val) || 0;
        total_value += parseFloat(tot_val) || 0;
      }
    }
  }

  // Update summary display
  $('.total_quantity').html(total_qty);
  $('#summary_total_qty').html(total_qty);
  $('#summary_total_value').html(total_value.toFixed(2));

  // Keep hidden fields in sync
  $('#hidden_total_qty').val(total_qty);
  $('#hidden_total_amt').val(total_value.toFixed(2));
}

/* ------------------------------------------------------------------ */
/* REMOVE ROW                                                           */
/* ------------------------------------------------------------------ */
function damage_removerow(rowcount) {
  $('#row_' + rowcount).remove();
  damage_total();
  if (typeof failed !== 'undefined') {
    failed.currentTime = 0;
    failed.play();
  }
}

/* ------------------------------------------------------------------ */
/* RESTRICT DUPLICATE / OVER-STOCK  (mirrors sales.js)                 */
/* ------------------------------------------------------------------ */
function damage_restrict_quantity(item_id) {
  var rowcount = parseInt($('#hidden_rowcount').val(), 10);
  var avail_qty = 0;
  var count_item_qty = 0;

  for (var i = 1; i <= rowcount; i++) {
    if (document.getElementById('tr_item_id_' + i)) {
      var selected_id = $('#tr_item_id_' + i).val().trim();
      if (parseFloat(item_id) === parseFloat(selected_id)) {
        avail_qty = parseFloat($('#tr_available_qty_' + i).val().trim());
        count_item_qty += parseFloat($('#td_data_' + i + '_3').val().trim());
      }
    }
  }

  if (avail_qty !== 0 && count_item_qty >= avail_qty) {
    toastr['warning']('Only ' + avail_qty + ' items in stock!');
    failed.currentTime = 0;
    failed.play();
    return false;
  }
  return true;
}
