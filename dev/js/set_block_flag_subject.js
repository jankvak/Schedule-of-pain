$(document).ready(function() {
		
        // na zaciatku skryjeme formular na zadavanie komentara
        $('#comment').hide();
        
		// pri zmene checkboxu sa vykona asynchronna HTTP poziadavka
		$('.blocking').change(function() {
			var checked = $(this).attr("checked");
			var id = $(this).attr("value");
			var opt = {
				data: "subjectId="+id +
					  "&block=" + checked,
				// ideme menit flag blokovania
				url: 'ape/subjects/changeBlockStatus',
				type: 'POST',
				success: function(data) {
					// ak sme zablokovali, tak zobrazime formular na komentar
					// inak ten formular skryjeme (ak nejaky bol)
					if (checked) {
                        var temp = data.split("|");
                        showComment(temp[0], temp[1], id);
                    }
					else {
                        $('#comment').hide();
                    }
				}
			}
			$.ajax(opt);
		});
        
        // klik na tlacidlo ulozit vo formulari
        // cez ajax ulozime komentar do db
        $('#saveComment').click(function() {
            var id = $('#subjectId').val();
            var comment = $('#blockComment').val();
            var opt = {
				data: "subjectId="+id +
					  "&comment=" + comment,
				// ideme ulozit komentar
				url: 'ape/subjects/saveComment',
				type: 'POST',
				success: function(data) {
                    // uspesne ulozenie, skryjeme formular a vratime sa na predmet, ktory sme blokovali
                    $('#comment').hide();
                    $(document).scrollTo( $('#' + id), 0);
				}
			}
			$.ajax(opt);
        });
        
        // klik na tlacidlo zrusit vo formulari
        // skryt formular a vratit sa na predmet, ktory sme blokovali
        $('#cancel').click(function() {
            $('#comment').hide();
            $(document).scrollTo( $('#' + $('#subjectId').val()), 0);
        });
});

// metoda na zobrazenie formulara, dostane nazov, komentar a id predmetu, ktoreho sa to tyka
function showComment(subjectName, blockComment, subjectId) {
    
    // zobrazime formular
    $('#comment').show();
    
    // nastavime data pre formular
    $('#subjectId').val(subjectId);
    $('#blockComment').val(blockComment);
    $('#commentTitle').html("Práve ste zablokovali možnosť preberania požiadaviek pre predmet: " + subjectName);
    
	//nastavime focus na textareu
	$('#blockComment').focus();
    
    // zaskrolujeme uplne dole na <div> s id comment
	$(document).scrollTo( $('#comment'), 0);
}