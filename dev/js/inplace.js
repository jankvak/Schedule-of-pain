$.fn.inplace = function(settings) {
	defaults = {
		updateurl: 'edit.php',
		edittext: 'Upravit',
		ignore: '.action',
		spliton: '_'
	}

	settings = $.extend(defaults, settings); //overwrite the defaults with provided setings

	$this = $(this);

	//$(".action").hide();

	$("tr", $this).click(function() {
		var $row = $(this);

		if($row.hasClass("__inplace_edit")) return;

		$row.addClass("__inplace_edit");

		id = $row.attr("id").split(settings.spliton)[1];
		$row.prepend("<input type='hidden' name='id' value='" + id + "'/>");

		$("td:not(" + settings.ignore + ")", $row).each(function() {
			$td = $(this);

			if($("input", $td).length > 0) {
				$("input", $td).removeAttr("disabled");
				$td.addClass("__inplace_was_input");
			} else {
				$oValue = $td.html();
				$td.html("<input type='text' name='" + parse_name($td.attr("class")) + "' value='" + $oValue  + "'/>");
			}
		});
	
	$("input:last", $row).parent().after("<td class='__inplace_button'><input id='__inplace_update_" + $row.attr("id") + "' class='__inplace_ignore' type='submit' value='ok'/> <input id='__inplace_cancel_" + $row.attr("id") + "' class='__inplace_ignore' type='submit' value='zrusit'/></td>");

		$("#__inplace_cancel_" + $row.attr("id")).click(function() {
			cancel_edit($row.attr("id"));
			$row.removeClass("__inplace_edit");
			return false;
		});

		$("#__inplace_update_" + $row.attr("id")).click(function() {

			$(".__inplace_button").addClass('updating')

			$.post(settings.updateurl, build_data($row), function(data) {
				if(data.status != "ok") {
					alert("Chyba pri ukladani");
				}
			}, "json");

			cancel_edit($row.attr("id"));
			$row.removeClass("__inplace_edit");
			return false;
		});
	});

	function cancel_edit(rowid) {
		
		$("#" + rowid + " td:not(" + settings.ignore + ")").each(function() {
			$td = $(this);

			if($td.hasClass("__inplace_was_input")) {
				$("input", $td).attr("disabled", "disabled");
			} else {
				$td.html($("input", $td).val());
			}
		});

		$("#" + rowid + " .__inplace_button").remove();
	}

	function parse_name(classes) {
		name = "";

		$.each(classes.split(" "), function(i, e) {
			if((splitArray = e.match("^name:(.*)")) != null) {
				name = splitArray[1];
				return false;
			}
		});

		return name;
	}

	function build_data($row) {
		o = new Object();

		$("input:not(.__inplace_ignore)", $row).each(function() {
			$input = $(this);

			name = $input.attr("name");
			val = $input.val();

			eval("o." + name + " = '" + val + "'");
		});

		return o;
	}

	return $this;
}

