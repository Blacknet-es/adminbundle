
// AÑADIDO Data-Action
$(document).on('click', '.btn[data-action]', function(e) {
    e.preventDefault();
    var action = 'action_' + $(this).data('action');
    if(typeof window[action] === 'function') {
        window[action]($(this));
    }
})