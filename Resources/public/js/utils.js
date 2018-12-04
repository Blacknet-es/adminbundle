
// AÑADIDO Data-Action
$(document).on('click', '.actions a[data-action], .btn[data-action]', function(e) {
    e.preventDefault();
    var action = 'action_' + $(this).data('action');
    if(typeof window[action] === 'function') {
        console.log($(this));
        window[action]($(this));
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


function action_editar_campo(){
    var $editableRow = $storedElement.find('dd.editableField');
    var $editInput = $editableRow.find('div.editInput');
    var $value = $editableRow.find('span.propertyValue');
    var type = $editableRow.data('type');

    // Si es date le realizamos un trato especial
    if(type == 'datetime'){
        $editInput.find('input[name*="_date"]').val($editableRow.data('datevalue'));
        $editInput.find('input[name*="_time"]').val($editableRow.data('timevalue'));
    }else if(type == 'date'){

    }else{
        $editInput.find('input,select').val($editableRow.data('originalvalue'));
    }

    $editInput.show();
    $value.hide();

    $modalEditarCampo.modal('hide');
}