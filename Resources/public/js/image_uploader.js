$(document).ready(function() {
    $('.image_preview.select-image-button img').each(function() {
        var src = $(this).attr('src');
        if(src) {
            $(this).attr('src', src.replace('placehold.io', 'placehold.it'));
        }
        $(this).parent().parent().parent().find('label.control-label').css('display', 'none');
    });
    $('.image_add_button').each(function() {
        $(this).parent().find('label.control-label').css('display', 'none');
    });
});