function activateButton(object){
    object.querySelector('.idnkcsp-cart').style.display = "block";
};

function disableButton(object){
    object.querySelector('.idnkcsp-cart').style.display = "none";
};

$(window).scroll(function() {
    var top_of_element = $("#idnkcsp-block").offset().top;
    var bottom_of_element = $("#idnkcsp-block").offset().top + $("#idnkcsp-block").outerHeight();
    var bottom_of_screen = $(window).scrollTop() + $(window).innerHeight();
    var top_of_screen = $(window).scrollTop();

    if ((bottom_of_screen > top_of_element) && (top_of_screen < bottom_of_element)){
        $(".idnkcsp-scroll-menu").css("display", "block");
    } else {
        $(".idnkcsp-scroll-menu").css("display", "none");
    }
});
