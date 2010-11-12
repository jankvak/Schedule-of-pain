	$(document).ready(function() {
	   	$('#tabs div').click(function(){
	   		if(this.id == "switch1"){
					$('#tab1').attr('class',"active");
					$('#tab2').attr('class',"passive");
					$('#tab3').attr('class',"passive");
					$('#mainForm div.a').show();
					$('#mainForm div.b').hide();
					$('#mainForm div.c').hide();
	   		}
	   		if(this.id == "switch2"){
	   				$('#tab1').attr('class',"passive");
					$('#tab2').attr('class',"active");
					$('#tab3').attr('class',"passive");
					$('#mainForm div.a').hide();
					$('#mainForm div.b').show();
					$('#mainForm div.c').hide();
			}
	   		if(this.id == "switch3"){
	   				$('#tab1').attr('class',"passive");
					$('#tab2').attr('class',"passive");
					$('#tab3').attr('class',"active");
					$('#mainForm div.a').hide();
					$('#mainForm div.b').hide();
					$('#mainForm div.c').show();
	   		}
	   	});
    })