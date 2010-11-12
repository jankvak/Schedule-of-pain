$(document).ready(function() {
	$("label[for=username]").hide();
	$("input[name=username]").hide();

	$("form.profile fieldset").prepend("<p><label for='fullname'>Meno</label><input id='fullname' type='text'/><br/></p>");

	$("#fullname").autocomplete("administrator/users/usersearch", {
		minChars: 3,
		matchContains: true,
		highlightItem: true,
		width: 300,
		formatItem: function(row, i, max, term) {
			splitRow = row.toString().split(",");
			return term + "<br/><span style='font-size: 80%'>UID: " + splitRow[splitRow.length - 1] + "</span>";
		}
	});

	$("#fullname").result(function(event, data, formatted) {
		if(data) {
			$("input[name=username]").val(data[1]);
		}
	});
});
