zebra = function($) {
	$(document).ready(function() {
		var odd = 1;
		$("table tbody tr").each(function() {
			clazz = odd ? 'odd' : 'even';
			odd = 1 - odd;

			$(this).addClass(clazz);
		});
	});
}(jQuery);

$.tablesorter.addParser({ 
    id: 'dates-sk', 
    is: function(s) { 
        return false; 
    }, 
    format: function(s) { 
        return s.substring(6,10)+s.substring(3,5)+s.substring(0,2)+s.substring(11);
    }, 
    type: 'text' 
}); 

// @table 	- referencia na tabulku
// @index 	- jednoznacny identifikator pageru 
// @sizes 	- mozne velkosti stranok
// @sel	  	- index predvolenej velkosti stranok
function createPager(table, index, sizes, sel) {
	if (sel < 0)
		sel = 0;
	if (sizes.length <= sel)
		sel = sizes.length - 1;
	var pagerID = 'pager_' + index;
	// zisti pocet stlpcov
	colspan = $("thead th", table).size();
	
	html = '<tfoot id="' + pagerID + '" class="header" "style="height:10px; text-align:center;width:50%;">'+
	       '<tr><td colspan='+ colspan+ '>'+
	       '<form action="" style="border-bottom-color: transparent;">'+
	       '<img alt="Začiatok" src="images/first.png" class="first"/>'+
	       '<img alt="Predchádzajúci" src="images/prev.png" class="prev"/>'+
	       '<input type="text" class="pagedisplay"/>'+
	       '<img alt="Ďalší" src="images/next.png" class="next"/>'+
	       '<img alt="Koniec" src="images/last.png" class="last"/>'+
	       '<select class="pagesize">';
	for (i in sizes)
	{
		html += '<option value="'+sizes[i]+'"';
		if (i == sel) html += ' selected="selected"';
		html += '>'+sizes[i]+'</option>';
	}
	html += '</select></form></td></tr></tfoot>';
	
	table.append(html);	
	table.tablesorterPager( {
		container : $('#' + pagerID),
		size : sizes[sel]
	});
}

$(document).ready(function() {
	$("table.sorted-table").tablesorter();
	$("table.filtered").each(function(index){
		boxName = 'filter-box-'+index;
		clearName = 'filter-clear-'+index;
		filter = 'Hľadaj: <input name="filter" id="'+boxName+'" value="" maxlength="30" size="30" type="text">';
		filter += '<input id="'+clearName+'" type="submit" value="Zmazať"/>';
		$(this).before(filter);
		$(this).tablesorterFilter({filterContainer: "#"+boxName,
                            filterClearContainer: "#"+clearName});
	});
	$("table.paged-table").each(function(index) {
			// default hodnoty ak budeu prepise ich
			sizes = [ 10, 25, 50 ];
			selpagesize = 0;
			// ak je to mozne z metadat vytiahne velkosti stranok
			if ($.metadata) {
				if ($(this).metadata().pagesizes)
					sizes = $(this).metadata().pagesizes;
				// defaultna velkost stranky po nacitani
				if ($(this).metadata().selpagesize)
					selpagesize = $(this).metadata().selpagesize;
			}
			createPager($(this), index, sizes, selpagesize);
		});
});
