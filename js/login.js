login = function($) {

    $(document).ready(function() {
        $("input.login").focus(function() {
            $(".hint-login").show();
            $(this).parent("p").css("background-color", "#fffddd");
			$(".hint").css("background-color", "#fffddd");
        }).blur(function() {
            $(".hint-login").hide(); 
            $(this).parent("p").css("background-color", "#fff");
			$(".hint").css("background-color", "#fff");
		}).focus();   

        $("input.password").focus(function() {
            $(".hint-password").show();
            $(this).parent("p").css("background-color", "#fffddd");
			$(".hint").css("background-color", "#fffddd");
        }).blur(function() {
			$(".hint-password").hide();
			$(this).parent("p").css("background-color", "#fff");
			$(".hint").css("background-color", "#fff");
        });
    });

}(jQuery);
