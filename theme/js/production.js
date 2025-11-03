$(document).ready(function() {
    // Add Production Batch
    $("#save").on("click", function(e) {
        e.preventDefault();
        var base_url = $("#base_url").val();
        var form = $("#production-form");
        var data = form.serialize();

        $.ajax({
            type: 'POST',
            url: base_url + 'production/save',
            data: data,
            success: function(result) {
                if (result == "success") {
                    window.location.href = base_url + "production";
                } else {
                    toastr["error"](result);
                }
            }
        });
    });

    // Check Stock Availability
    $("#check_availability").on("click", function() {
        var base_url = $("#base_url").val();
        var recipe_id = $("#recipe_id").val();
        var batch_quantity = $("#batch_quantity").val();

        if (recipe_id == "") {
            toastr["error"]("Please select a recipe.");
            return;
        }
        if (batch_quantity == "" || batch_quantity <= 0) {
            toastr["error"]("Please enter a valid batch quantity.");
            return;
        }

        $.ajax({
            type: 'GET',
            url: base_url + 'production/get_recipe_details/' + recipe_id,
            dataType: 'json',
            success: function(result) {
                var ingredients_table = $("#ingredients_table tbody");
                ingredients_table.empty();
                var all_available = true;
                $.each(result.ingredients, function(index, item) {
                    var required_qty = item.required_qty * batch_quantity;
                    var status = (item.available_qty >= required_qty) ? '<span class="label label-success">Available</span>' : '<span class="label label-danger">Insufficient</span>';
                    if (item.available_qty < required_qty) {
                        all_available = false;
                    }
                    var row = '<tr>' +
                        '<td>' + item.item_name + '</td>' +
                        '<td>' + required_qty + '</td>' +
                        '<td>' + item.available_qty + '</td>' +
                        '<td>' + item.unit + '</td>' +
                        '<td>' + status + '</td>' +
                        '</tr>';
                    ingredients_table.append(row);
                });

                if (!all_available) {
                    $("#save").prop("disabled", true);
                    toastr["error"]("Insufficient stock for some ingredients. Please adjust the batch quantity or restock the ingredients.");
                } else {
                    $("#save").prop("disabled", false);
                }
            }
        });
    });
});

function approve_production(id) {
    if (confirm("Are you sure you want to approve this production batch?")) {
        var base_url = $("#base_url").val();
        $.ajax({
            type: 'POST',
            url: base_url + 'production/approve/' + id,
            success: function(result) {
                if (result == "success") {
                    toastr["success"]("Production Batch Approved Successfully!");
                    $('#example2').DataTable().ajax.reload();
                } else {
                    toastr["error"](result);
                }
            }
        });
    }
}

function delete_production(id) {
    if (confirm("Are you sure you want to delete this production batch?")) {
        var base_url = $("#base_url").val();
        $.ajax({
            type: 'POST',
            url: base_url + 'production/delete/' + id,
            success: function(result) {
                if (result == "success") {
                    toastr["success"]("Production Batch Deleted Successfully!");
                    $('#example2').DataTable().ajax.reload();
                } else {
                    toastr["error"]("Failed to delete production batch. Please try again.");
                }
            }
        });
    }
}
