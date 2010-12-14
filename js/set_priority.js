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
		circular:true,
		scroll: 2
	});
	
	$( ".table" ).selectable({ 
		delay: 20,
		filter: ".editable",
		stop: function(event, ui)
		{ 
			//$(this).css("background-color","orange");
			//ui.helper.css("background-color","orange");
		//	$(this).find("div .ui-selected").html("pok");
			$(this).find("div .ui-selected").each(function(ind)
			{
				if($(this).hasClass("closed"))return;
				if( (!$(this).hasClass("1sel"))&&(!$(this).hasClass("2sel")) )
				{
				
				$(this).css("background-color","green");
				$(this).addClass("1sel");
				}
				else if($(this).hasClass("1sel"))
				{
				$(this).css("background-color","red");
				$(this).removeClass("1sel");
				$(this).addClass("2sel");
				}
				else if($(this).hasClass("2sel"))
				{
				$(this).css("background-color","#E6E6FA");
				$(this).removeClass("2sel");
				}
			});



		}
		
	});

	
	//inicializácia predmetu ako presunute¾ného prvku
	$( ".predmet" ).draggable({  
		appendTo: "body",
		helper: "clone",
		revert: "true",
		opacity: 0.6,
		distance: 1,
		
		start : function() {
        this.style.display="none";
        },
		
		stop : function() {
        this.style.display="";
		
        },
		});
	
	
//inicializácia kolonky v rozvrhu na akceptovanie predmetu
	$(".editable").droppable({
		tolerance:"pointer" ,

      drop: function(ev, ui) { 
		$(this).html(ui.draggable.attr("id") );
		
		$(this).css("background-color",ui.draggable.css("background-color"));
		$(this).addClass("closed");
		//alert( ui.draggable.find("div").html() );
		
		ui.helper.remove();
		ui.revert("false");
		
	  }
    });
	
		
		$(".editable").click(function(){
			//$(this)
			if($(this).hasClass("closed"))return;
			if( (!$(this).hasClass("1sel"))&&(!$(this).hasClass("2sel")) )
			{
				
				$(this).css("background-color","green");
				$(this).addClass("1sel");
			}
			else if($(this).hasClass("1sel"))
			{
				$(this).css("background-color","red");
				$(this).removeClass("1sel");
				$(this).addClass("2sel");
			}
			else if($(this).hasClass("2sel"))
			{
				$(this).css("background-color","#E6E6FA");
				$(this).removeClass("2sel");
			}
			//$(this).addClass("1sel");
					
			
		});
	})
