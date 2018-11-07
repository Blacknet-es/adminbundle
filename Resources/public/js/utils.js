
// AÑADIDO Data-Action
$(document).on('click', '.actions a[data-action], .btn[data-action]', function(e) {
    e.preventDefault();
    var action = 'action_' + $(this).data('action');
    if(typeof window[action] === 'function') {
        console.log($(this));
        window[action]($(this));
    }
});