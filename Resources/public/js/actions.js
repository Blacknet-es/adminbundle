$(document).ready(function() {
    $('.action-button').hover(function() {
        if($(this).parent().children('.action-label').text())
            $(this).parent().children('.action-label').animate({opacity: 1});
    }, function() {
        $(this).parent().children('.action-label').animate({opacity: 0});
    });
    $('.actions a[data-toggle="admin-tooltip"]').hover(function() {
        var tooltip = '<span class="action-label" style="position:absolute;left: 35px;top:0;font-size:0.8em;z-index:10;">' + $(this).attr('title') + '</span>';
        $(this).append(tooltip);
        $(this).children('.action-label').animate({opacity: 1});
    }, function() {
        $(this).children('.action-label').animate({opacity: 0});
        $(this).children('.action-label').remove();
    });
});