$(document).ready(function()
{	
	// ak sa zmeni stav checkall checkboxu potom zmeni na dany stav vsetky vnorene
	$(".checkall").change(function()
	{
		id = $(this).attr("id").split("_")[1];
		checked = $(this).attr("checked");
		$("#checkarea_"+id+" input").each(function(){
			$(this).attr("checked", checked);
		});
	});
	$(".checkarea input").change(function(){				
		// z mena ziska layout
		layout = $(this).attr("id")[0];
		all = $("#checkarea_"+layout+" input").length;
		checked = $("#checkarea_"+layout+" input:checked").length;
		$("#checkall_"+layout).attr("checked", all == checked);
	}); 
	// zavolame nech sa spracuje hned po nacitani
	$(".checkarea input").change();
});