function disable_req(rozl, poz)
{
    disable("#lecture"+poz+rozl+" input");
    disable("#lecture"+poz+rozl+" select");
    disable("#lecture"+poz+rozl+" textarea");
}

function enable_req(rozl, poz)
{
    enable("#lecture"+poz+rozl+" input");
    enable("#lecture"+poz+rozl+" select");
    enable("#lecture"+poz+rozl+" textarea");
}

function disable_all(rozl)
{
    for (i=1;i<=3;i++)
        disable_req(rozl, i);
}

function enable_req_till(rozl, poz)
{
    disable_all(rozl);
    for (i=1;i<=poz;i++)
        enable_req(rozl, i);
}

$(document).ready(function() {

    //definicie
    $('#lecture_count_a').change(function(){
        val = parseInt($('#lecture_count_a').val());
        fade_requirements('a', val);
        enable_req_till('a', val);
    });
	   	
    $('#lecture_count_b').change(function(){
        val = parseInt($(this).val());
        fade_requirements('b', val);
        enable_req_till('b', val);
    });
		
    $('#lecture_count_c').change(function(){
        val = parseInt($('#lecture_count_c').val());
        fade_requirements('c', val);
        enable_req_till('c', val);
    });

    // defaultne akcie
    $("#lecture_count_a").change();
    enable(".a .core_head input, .a .core_head select");
})
