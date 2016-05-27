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
        processListaForm();
    });

    $(document).on('change', 'input.search-field', function() {
        $listaForm.find('.search').val($(this).val());
        processListaForm();
    });

    $(document).on('click', '.toggle-value', function(e) {
        e.preventDefault();
        var row = $(this).closest('tr');
        // We need the element real ID, the position of the column being toggled, and the desired value:
        var id = row.children('td').first().text();
        var col = row.children().index($(this).closest('td'));
        var value = parseInt($(this).data('value')) ? 0 : 1;
        $listaForm.find('.toggle_field').val(id + ',' + col + ',' + value);
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
            $('#content > .container').html(data);
        });
    }

});
