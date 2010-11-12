	$(document).ready(function() {
		$('#rem2').click(function(){
			$('#tab2').hide();
			$('#mainForm .b').hide();
			$('#mainForm .a').show();
			$('#tab1').attr('class',"active");
			$('#tab2').attr('class',"passive");
			$('#tab3').attr('class',"passive");
			
			$(".b input:checkbox").removeAttr("checked");
            $(".b input, .b select, .b textarea").attr("disabled", "disabled");
		});
		
		$('#rem3').click(function(){
			$('#tab3').hide();
			$('#mainForm .c').hide();
			$('#mainForm .a').show();
			$('#tab1').attr('class',"active");
			$('#tab2').attr('class',"passive");
			$('#tab3').attr('class',"passive");

			$(".c input:checkbox").removeAttr("checked");
            $(".c input, .c select, .c textarea").attr("disabled", "disabled");
	   	});
	})
