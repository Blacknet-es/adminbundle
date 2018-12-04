$(document).ready(function() {
    var $listaForm = $('#listaForm');

    $(document).on('click', '.pagination a[data-page]', function() {
        if ($(this).hasClass('active')) {
            return false;
        }

        var page = $(this).data('page');

        $listaForm.find('.pag').val(page);
        processListaForm();
    });

    $(document).on('click', '.page-size-selector', function(e) {
        e.preventDefault();
        $listaForm.find('.max').val($(this).data('value'));
        // Cuando se cambie el numero de elementos por pagina cambiamos a la pagina 1
        $listaForm.find('.pag').val(1);
        processListaForm();
    });

    $(document).on('change', 'input.search-field', function() {
        $listaForm.find('.search').val($(this).val());
        processListaForm();
    });

    $(document).on('click', '.update-value', function(e) {
        e.preventDefault();
        var row = $(this).closest('tr');
        // We need the element real ID, the position of the column being toggled, and the desired value:
        var id = row.data('row-id');
        var col = $(this).find('[data-original-col]').data('original-col');
        var value = parseInt($(this).data('value')) ? 1 : 0;
        $listaForm.find('.update_field').val(id + ',' + col + ',' + value);
        processListaForm();
    });

    $(document).on('click', '.column-header-anchor.sortable', function() {
        var previousOrder =  $listaForm.find('.order_column').val();
        var direction = 0;
        if(previousOrder == $(this).data('value')) {
            direction = parseInt($listaForm.find('.order_direction').val()) ? 0 : 1;
        }
        $listaForm.find('.order_column').val($(this).data('value'));
        $listaForm.find('.order_direction').val(direction);
        processListaForm();
    });

    function processListaForm()
    {
        $.ajax({
            url: location.href,
            type: 'post',
            data: $listaForm.serialize()
        }).done(function (data) {
            $('#main > div').html(data);
        });
    }

});
