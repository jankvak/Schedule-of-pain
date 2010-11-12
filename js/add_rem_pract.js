function disable_group(id)
{
    // deaktivuje vsetky vstupne polozky, aj pre Typ skupiny 2
    // neskryva ich lebo enable_group_1 sa pozrie a ak su aktivne aktivuje
    disable('#lecture'+id+' input');
    disable('#lecture'+id+' select');
    disable('#lecture'+id+' textarea');
}

// deaktivuje vsetky vstupy kazdej poziadavky daneho rozlozenia
function disable_all(rozl)
{	
    for (i=1;i<=3;i++)
        disable_group(i+rozl);
}

// aktivuje vsetky prvky grupy 1 daneho rozlozenia a poziadavky
// ak je visible aj grupa 2 tak aktivuje aj jej prvky
function enable_group(rozl, poz)
{
    id = poz+rozl; // id grupy+lecture
    // aktivacia common veci a grupy 1
    enable('#group'+id+'_1 input');
    enable('#group'+id+'_1 select');
    enable('#lecture'+id+' textarea');
    enable('#lecture'+id+' .common_input');
    // ak je grupa 2 viditelna tak aj jej prvky aktivuje
    enable('#group'+id+'_2:visible input');
    enable('#group'+id+'_2:visible select');
}

function enable_group_till(rozl, poz)
{
    disable_all(rozl);
    for (i=1;i<=poz;i++)
        enable_group(rozl, i);
}

$(document).ready(function() {

    $('#lecture_count_a').change(function(){
        val = parseInt($(this).val());
        fade_requirements('a', val);
        enable_group_till('a', val);        
    });
	   	
    $('#lecture_count_b').change(function(){
        val = parseInt($(this).val());
        fade_requirements('b', val);
        enable_group_till('b', val);
    });
		
    $('#lecture_count_c').change(function(){
        val = parseInt($(this).val());
        fade_requirements('c', val);
        enable_group_till('c', val);
    });

    // standardne aktivuje iba pre grupu (standardne ako keby zmenil pocet)
    // ale az po tom co su definovane funkcie
    $("#lecture_count_a").change();
    enable(".a .core_head input, .a .core_head select");
})
        