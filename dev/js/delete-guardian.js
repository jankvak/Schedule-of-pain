$(document).ready(function() {
	$("a[href*='delete']").click(function() {
		return confirm("Naozaj chcete zmazat tento zaznam?");	
	});
});
