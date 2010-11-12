$(document).ready(function() {	
    $('#add1').click(function(){
        $('#tab2').show();
        $('#mainForm .b').show();
        $('#mainForm .a').hide();
        $('#mainForm .c').hide();
        $('#tab1').attr('class',"passive");
        $('#tab2').attr('class',"active");
        $('#tab3').attr('class',"passive");

        $(".b .core_head input, .b .core_head select").removeAttr("disabled");

        $('#lecture_count_b').change();
    });
		
    $('#add2').click(function(){
        $('#tab3').show();
        $('#mainForm .c').show();
        $('#mainForm .b').hide();
        $('#mainForm .a').hide();
        $('#tab1').attr('class',"passive");
        $('#tab2').attr('class',"passive");
        $('#tab3').attr('class',"active");
            
        $(".c .core_head input, .c .core_head select").removeAttr("disabled");
			
        $('#lecture_count_c').change();
    });
})
