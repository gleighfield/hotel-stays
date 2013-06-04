$(function () {
    console.log("Ready...");
    $('#import').click(function (e) {
        if ($('#offer').val() == 'empty') {
            alert("Please select an offer");
        }
        if ($('#file').val() == '') {
            alert("Please select a CSV file");
        }
        if ($('#offer').val() == 'empty' || $('#file').val() == '') {
            return false;
        }
        var msg = "By importing a CSV file you are aware that this will add to the existing rows. \n\nThis CANNOT BE UNDONE.\n\nAre you sure?";

        if($('#newImport').attr('checked')) {
            msg = "By importing a CSV file you are aware that all existing listings for the selected offer will be deleted, and replaced with the entries in this CSV file.\n\nThis CANNOT BE UNDONE.\n\nAre you sure?"
        }

        var x = confirm(msg);
        if (!x) {
            return false;
        }
    });
});