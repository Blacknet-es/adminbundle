$(document).ready(function() {
    var $listaForm = $('#listaForm');

    $(document).on('click', '.open-search-filters', function(e) {
        e.preventDefault();
        $(this).hide();
        $('.close-search-filters').show();
        $('.filters-row').animate({height: '60'}).css({ overflow: 'auto'});
    });

    $(document).on('click', '.close-search-filters', function(e) {
        e.preventDefault();
        $(this).hide();
        $('.open-search-filters').show();
        $('.filters-row').animate({height: '0'}).css({ overflow: 'hidden'})
    });

    $(document).on('click', '.clear-search-filters', function(e) {
        e.preventDefault();
        $(this).closest('.lista-card').find('.search-filter').each(function(e) {
            $(this).val('');
        });
        processListaForm.call(this);
    });

    $(document).on('click', 'a.download-file', function(e) {
        e.preventDefault();
        $(this).closest('.lista-card').find('form.list-download').submit();
    });

    $(document).on('click', '.pagination a[data-page]', function() {
        if ($(this).hasClass('active')) {
            return false;
        }

        var page = $(this).data('page');

        $listaForm.find('.pag').val(page).attr('value', page);
        processListaForm.call(this);
    });

    $(document).on('click', '.page-size-selector', function(e) {
        e.preventDefault();
        $listaForm.find('.max').val($(this).data('value'));
        // Cuando se cambie el numero de elementos por pagina cambiamos a la pagina 1
        $listaForm.find('.pag').val(1);
        processListaForm.call(this);
    });

    $(document).on('change', 'input.search-field, .search-filter', function() {
        $listaForm.find('.search').val($(this).closest('.lista-card').find('input.search-field').val());
        processListaForm.call(this);
    });

    $(document).on('click', '.update-value', function(e) {
        e.preventDefault();
        var row = $(this).closest('tr');
        // We need the element real ID, the position of the column being toggled, and the desired value:
        var id = row.data('row-id');
        var col = $(this).find('[data-original-col]').data('original-col');
        var value = parseInt($(this).data('value')) ? 1 : 0;
        $listaForm.find('.update_field').val(id + ',' + col + ',' + value);
        processListaForm.call(this);
    });

    $(document).on('click', '.column-header-anchor.sortable', function() {
        var previousOrder =  $listaForm.find('.order_column').val();
        var direction = 0;
        if(previousOrder == $(this).data('value')) {
            direction = parseInt($listaForm.find('.order_direction').val()) ? 0 : 1;
        }
        $listaForm.find('.order_column').val($(this).data('value'));
        $listaForm.find('.order_direction').val(direction);
        processListaForm.call(this);
    });

    let proccesing = false;
    function processListaForm()
    {
        if (proccesing == true) {
            console.log('para el carro');
            return false;
        }
        var filtersField = $(this).closest('.lista-card').find('.filters_data');
        var filters = filtersField.val();
        if(filters && filters != '') {
            filters = JSON.parse(filters);
            $(this).closest('.lista-card').find('.search-filter').each(function() {
                var val = $(this).val();
                if(filters[$(this).data('field')]) {
                    filters[$(this).data('field')].value = val;
                }
            });
        }

        filtersField.val(JSON.stringify(filters));
        console.log($listaForm.html());
        proccesing = true;
        $.ajax({
            url: location.href,
            type: 'post',
            data: $listaForm.serialize()
        }).done(function (data) {
            $('#main > div').html(data);
            setTimeout(function() {
                proccesing = false;
            }, 300);
        });
    }

    $(document).on('change', '#check-all', function(e) {
        var allCheckboxes = $(this).closest('.lista-card').find('table tbody .bulk-check input[type="checkbox"]');
        var allCheckedCheckboxes = $(this).closest('.lista-card').find('table tbody .bulk-check input[type="checkbox"]:checked');
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
