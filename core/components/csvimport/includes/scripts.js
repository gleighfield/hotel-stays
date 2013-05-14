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

		var x = confirm("By importing a CSV file you are aware that all existing listings for the selected offer will be deleted, and replaced with the entries in this CSV file.\n\nThis CANNOT BE UNDONE.\n\nAre you sure?");
		if (!x) {
			return false;
		}
	});
});