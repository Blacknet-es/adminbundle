
// AÑADIDO Data-Action
$(document).on('click', '.actions a[data-action], .btn[data-action]', function(e) {
    e.preventDefault();
    var action = 'action_' + $(this).data('action');
    if(typeof window[action] === 'function') {
        console.log($(this));
        window[action]($(this));
    }
});

$('body').on('click', '[data-ajax-action]', function() {
    var $e = $(this);
    var $modal = $('#TrendixAdminAjaxConfirmModal');
    var $btn = $('#btnTrendixAdminAjaxConfirmModal');

    if ($e.data('confirm') == true) {
        $modal.find('.modal-title').html($e.data('title'));
        $modal.find('.modal-body').html($e.data('message'));
        $btn.data('ajax-action', $e.data('ajax-action')).attr('data-ajax-action', $e.data('ajax-action'));

        $modal.modal('show');
        return false;
    }

    $.ajax({
        url: $e.data('ajax-action'),
        type: 'post'
    }).done(function(data) {
        handleResponse(data);
    });

    function handleResponse(data)
    {
        if (data.type == 'OK') {
            if (data.reload != undefined && data.reload) {
                window.location.reload();
            }
        }
        return true;
    }
});

$(document).on('click', '.confirm-edit-modal', function(e) {
    e.preventDefault();

    var id = '#' + $(this).data('id');
    var modalId = id + '_modal';
    var disabledID = id + '_disabled input, ' + id + '_disabled textarea';
    var button = id + '_disabled .add-on a[data-toggle]';
    var button2 = id + '_disabled .save-confirm-form';
    $(modalId).modal('hide');
    $(disabledID).removeAttr('disabled');
    $(disabledID).change(function() {
        $(id).val($(this).val());
    });
    $(button).addClass('d-none');
    $(button2).removeClass('d-none');

    //$(id).removeClass('d-none');
    return;
});

$(document).on('click', '.save-confirm-form', function(e) {
    $(this).closest('form').submit();
});
