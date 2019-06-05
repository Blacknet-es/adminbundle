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

    $(document).on('click', 'a.download-file', function(e) {
        e.preventDefault();
        console.log('ee');
        console.log($(this).closest('form'));
        $(this).closest('.lista-card').find('form.list-download').submit();
    });
});/* aa */
$(document).on('click', '.actions a[data-action], .btn[data-action]', function(e) {
    e.preventDefault();
    var action = 'action_' + $(this).data('action');
    if(typeof window[action] === 'function') {
        console.log($(this));
        window[action]($(this));
    }
});
