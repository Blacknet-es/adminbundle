$(document).ready(function() {
    $('.fileupload').fileupload({
        dataType: 'json',
        done: function (e, data) {
            $('#loading').hide();
            $('input[name="' + $(this).data("target") + '"]').val(data.result.file);
            $('.upload-confirm').removeClass('hidden');
        }
    });
    // Ocultamos el botón original.
    $(document).on('click', '.upload-button', function(e) {
        e.preventDefault();
        $(this).parent().parent().find('input.fileupload').trigger('click');
    });
});