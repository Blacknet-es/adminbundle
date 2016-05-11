$(document).ready(function() {
    var $listaForm = $('#listaForm');
    var actualPage = $('.pagination .active > a').data('page');
    var PrevPage = actualPage - 1;
    var NextPage = actualPage + 1;

    $(document).on('click', '.pagination a[data-page]', function() {
        if ($(this).hasClass('active')) {
            return false;
        }

        var page = $(this).data('page');
        if (page == 'prev') {
            page = PrevPage;
        }

        if (page == 'next') {
            page = NextPage;
        }

        $listaForm.find('.pag').val(page);
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
