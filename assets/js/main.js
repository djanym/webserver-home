(function ($) {
    $('#new_project_form').on('submit', function (event) {
        let data = $(this).serialize();
        console.log(data);

        $.ajax({
            type: "POST", url: "/", data: data, dataType: "json", encode: true
        }).done(function (data) {
            console.log(data);
        });

        event.preventDefault();
        // return false;
    });
})(jQuery);

function createProjectFolderForm() {
    let container = $();

}

function newProjectRequest() {

}