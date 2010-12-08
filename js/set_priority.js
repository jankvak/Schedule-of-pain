   	//tento skript robi 2 veci:
	//1.graficky zafarbuje jednotlive policka
	//2.pridava do kodu inputy pre oznacene policka v rozvrhu
	//	-tieto inputy pridava len pre 2 priority:
	//		preferovane: <input name="riadok_stlpec" value="1" type="hidden">
	//		nevyhovujuce: <input name="riadok_stlpec" value="2" type="hidden">
	//	- pre policka oznacene OK zrusi akykolvek input, cize defaultne su vsetky OK, ziaden input pre ne vsak nie je
	//	- to znamena ze do databazy budeme zapisovat len 2 mozne priority : preferovane(1) a nevyhovujuce(2)
	//	- name pozostava z hodnot riadok_stlpec (presne tak, ako su definovne id policok v html - preto ako name pridavam vsade this.id), pricom
	//		riadok nadobuda hodnoty {1,2,3,4,5}
	//		stlpec nadobuda hodnoty {1,2,3,4,5,6,7,8,9,10,11,12,13,14,15}
	
	$(document).ready(function() {	
	//inicializácia prvku carousel - posuvný kontajner pre predmety
	$("#carousel").jCarouselLite({
		btnNext: ".nextb",
		btnPrev: ".prevb",
		visible: 5,
		circular:true
	});
	
	//inicializácia predmetu ako presunute¾ného prvku
	$( ".predmet" ).draggable({  
		tolerance:"pointer" ,
		snapMode: "inner",
		helper: "clone",
		appendTo: "body"
		});
	//inicializácia kolonky v rozvrhu na akceptovanie predmetu
	$(".editable").droppable({
      drop: function(ev, ui) { 
		$(this).html(ui.draggable.attr("id"));
		
	  }
    });
		$('#sel_a').click(function(){
			if ($('#sel_a').hasClass("sel")) ;
			else {
				$('#sel_a').addClass("sel");
				$('#sel_b').removeClass("sel");
				$('#sel_c').removeClass("sel");
			}
		});
		$('#sel_b').click(function(){
			if ($('#sel_b').hasClass("sel")) ;
			else {
				$('#sel_b').addClass("sel");
				$('#sel_a').removeClass("sel");
				$('#sel_c').removeClass("sel");
			}
		});
		$('#sel_c').click(function(){
			if ($('#sel_c').hasClass("sel")) ;
			else {
				$('#sel_c').addClass("sel");
				$('#sel_b').removeClass("sel");
				$('#sel_a').removeClass("sel");
			}
		});

		$('.editable').click(function(){
			if ($('#sel_a').hasClass("sel")){
				$(this).attr('class',"col editable color_a");
				if ($("input[name='"+this.id+"']").length > 0) {
				    $("input[name='"+this.id+"']").attr('value','1');
				}	else {
				    $("form").append('<input name="'+this.id+'" value="1" type="hidden"/>');
				}
			}
			if ($('#sel_b').hasClass("sel")){
				$(this).attr('class',"col editable color_b");
				if ($("input[name='"+this.id+"']").length > 0) $("input[name='"+this.id+"']").remove();
			}
			if ($('#sel_c').hasClass("sel")){
				$(this).attr('class',"col editable color_c");
				if ($("input[name='"+this.id+"']").length > 0) $("input[name='"+this.id+"']").attr('value','2');
				else $("form").append('<input name="'+this.id+'" value="2" type="hidden"/>');
			}
		});
	})
