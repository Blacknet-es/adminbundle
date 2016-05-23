$(document).ready(function() {
    $('.action-button').hover(function() {
        $(this).parent().children('.action-label').animate({opacity: 1});
    }, function() {
        $(this).parent().children('.action-label').animate({opacity: 0});
    });
});