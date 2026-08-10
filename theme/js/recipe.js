$(document).ready(function () {
    // Add Recipe
    $("#save").on("click", function (e) {
        e.preventDefault();
        var base_url = $("#base_url").val();
        var form = $("#recipe-form");
        var data = form.serialize();

        $.ajax({
            type: 'POST',
            url: base_url + 'recipe/save',
            data: data,
            success: function (result) {
                if (result == "success") {
                    window.location.href = base_url + "recipe";
                } else {
                    toastr["error"](result);
                }
            }
        });
    });

    // Update Recipe
    $("#update").on("click", function (e) {
        e.preventDefault();
        var base_url = $("#base_url").val();
        var form = $("#recipe-form");
        var data = form.serialize();

        $.ajax({
            type: 'POST',
            url: base_url + 'recipe/update',
            data: data,
            success: function (result) {
                if (result == "success") {
                    window.location.href = base_url + "recipe";
                } else {
                    toastr["error"](result);
                }
            }
        });
    });

});

function multi_delete() {
    var base_url = $("#base_url").val();
    var selected = $('.column_checkbox:checked');

    if (selected.length === 0) {
        toastr["error"]("Please select at least one recipe.");
        return;
    }

    if (!confirm("Are you sure you want to delete the selected recipes?")) {
        return;
    }

    var ids = [];
    selected.each(function () {
        ids.push($(this).val());
    });

    $.ajax({
        type: 'POST',
        url: base_url + 'recipe/multi_delete',
        data: { 'ids[]': ids },
        traditional: true,
        success: function (result) {
            if (result.trim() === "success") {
                toastr["success"]("Selected recipes deleted successfully!");
                $('#example2').DataTable().ajax.reload();
                $('.delete_btn').addClass('hidden').hide();
                $('.group_check').prop('checked', false).iCheck('update');
            } else {
                toastr["error"](result);
            }
        },
        error: function () {
            toastr["error"]("Failed to delete selected recipes.");
        }
    });
}

function delete_recipe(id) {
    if (confirm("Are you sure you want to delete this recipe?")) {
        var base_url = $("#base_url").val();
        $.ajax({
            type: 'POST',
            url: base_url + 'recipe/delete/' + id,
            success: function (result) {
                if (result == "success") {
                    toastr["success"]("Recipe Deleted Successfully!");
                    $('#example2').DataTable().ajax.reload();
                } else {
                    toastr["error"]("Failed to delete recipe. Please try again.");
                }
            }
        });
    }
}
