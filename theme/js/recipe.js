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
