$(document).ready(function() {
    $('.action-button').hover(function() {
        if($(this).parent().children('.action-label').text())
            $(this).parent().children('.action-label').animate({opacity: 1});
    }, function() {
        $(this).parent().children('.action-label').animate({opacity: 0});
    });
});