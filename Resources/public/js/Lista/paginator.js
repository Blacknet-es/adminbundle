$(document).ready(function() {
    var $listaForm = $('#listaForm');

    $(document).on('click', '.pagination a[data-page]', function() {
        if ($(this).hasClass('active')) {
            return false;
        }

        var page = $(this).data('page');

        $listaForm.find('.pag').val(page);
        processListaForm($(this).closest('.lista-card'));
    });

    $(document).on('click', '.page-size-selector', function(e) {
        e.preventDefault();
        $listaForm.find('.max').val($(this).data('value'));
        processListaForm($(this).closest('.lista-card'));
    });

    $(document).on('change', 'input.search-field', function() {
        $listaForm.find('.search').val($(this).val());
        processListaForm($(this).closest('.lista-card'));
    });

    $(document).on('click', '.update-value', function(e) {
        e.preventDefault();
        var row = $(this).closest('tr');
        // We need the element real ID, the position of the column being toggled, and the desired value:
        var id = row.children('td').first().text();
        var col = row.children().index($(this).closest('td'));
        var column = $(this).closest('table').find('thead tr').children('th').eq(col).children('a');
        var colField = $(column).data("value");
        var value = parseInt($(this).data('value')) ? 1 : 0;
        $listaForm.find('.update_field').val(id + ',' + colField + ',' + value);
        processListaForm($(this).closest('.lista-card'));
    });

    $(document).on('click', '.column-header-anchor.sortable', function() {
        var previousOrder =  $listaForm.find('.order_column').val();
        var direction = 0;
        if(previousOrder == $(this).data('value')) {
            direction = parseInt($listaForm.find('.order_direction').val()) ? 0 : 1;
        }
        $listaForm.find('.order_column').val($(this).data('value'));
        $listaForm.find('.order_direction').val(direction);
        processListaForm($(this).closest('.lista-card'));
    });

    function processListaForm(card)
    {
        $.ajax({
            url: location.href,
            type: 'post',
            data: $listaForm.serialize()
        }).done(function (data) {
            card.parent().html(data);
        });
    }

    $(document).on('change', '#check-all', function(e) {
        var allCheckboxes = $(this).closest('.lista-card').find('table tbody .bulk-check input[type="checkbox"]');
        var allCheckedCheckboxes = $(this).closest('.lista-card').find('table tbody .bulk-check input[type="checkbox"]:checked');
        console.log(allCheckboxes.length);
        console.log(allCheckedCheckboxes.length);
        if(allCheckboxes.length == allCheckedCheckboxes.length && allCheckboxes.length != 0) {
            allCheckboxes.each(function() {
                $(this).prop('checked', false);
                $(this).removeAttr('checked');
            });
        } else {
            allCheckboxes.each(function() {
                $(this).prop('checked', true);
            });
        }
    });
});

// Funcionalidades para los bulk y advanced actions:

function action_useBulkAction($action_button) {
    var checkboxes = $($action_button.data('target') + ':checked');
    var ids = [];
    checkboxes.each(function() {
        ids.push($(this).val());
    });
    var action = $action_button.closest('.bulk-actions-select').find('.list-group-actions').val();

    if(action.indexOf('advanced:') != -1) {
        var actionId = action.replace('advanced:', '');
        $('#' + actionId + '-ids').val(ids.join(','));
        $('#advanced-bulk-modal-' + actionId).modal('show');
    } else {
        $.ajax({
            method: 'POST',
            url: action,
            data: {
                ids: ids
            }
        }).done(function(data) {
            if(data.success) {
                alert('Acción realizada correctamente');
                window.location.reload();
            } else if(data.message) {
                alert(data.message);
            } else {
                alert('Ha habido un error');
            }
        });
    }
}
