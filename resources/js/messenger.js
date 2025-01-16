/**
 * ------------------------------------------
 * Reuseable Functions
 * ------------------------------------------
 */

function imageFilePreview(input, selector) {
    if (input.files && input.files[0]) {
        let render = new FileReader();

        render.onload = function (e) {
            $(selector).attr("src", e.target.result);
        };

        render.readAsDataURL(input.files[0]);
    }
}

/**
 * ------------------------------------------
 * On Dum Load
 * ------------------------------------------
 */

$(document).ready(function () {
    $("#select_file").on("change", function () {
        imageFilePreview(this, ".profile-image-preview");
    });
});
