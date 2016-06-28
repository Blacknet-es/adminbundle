var galleries = {};

$(function(){
    $('.fileinput-button').click(function(event){
        if( $( event.target ).is( "span" ) )
        {
            // console.log('click');
            $('#image_upload_file').click();
            event.stopPropagation();
            return false;
        }
    });

});

function initializeImageManager(id, options){
    $('#image_upload_tabs li:nth-child(2)').hide();
    var url = Routing.generate('trendix_image_upload');
    var isGallery = typeof galleries[id] != 'undefined';

    $('#image_upload_file').fileupload({
        url: url,
        dataType: 'json',
        formData: {'config': JSON.stringify(options), 'isGallery': isGallery },
        dropZone: $('#image_upload_drop_zone'),
        done: function (e, data) {
            // console.log('uploaded');
            if(data.result.error){
                $('#image_upload_widget_error').text(data.result.error);
                $('#image_upload_widget_error').parent().removeClass('hidden');
            }
            else{
                $('#image_upload_widget_error').text('');
                $('#image_upload_widget_error').parent().addClass('hidden');
                // console.log(data.result, data.result['image_upload_file']);
                // $('#image_preview img').remove();
                // $('#image_preview').html('<img src="/'+data.result['image_upload_file'][0].url+'" id="image_preview_image"/>');
                $('#selected_image').val(data.result.file);
                initJCrop(id, options);
            }

        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#image_file_upload_progress .progress-bar').css(
                'width',
                progress + '%'
            );
        }
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');
    // $('#image_upload_file').bind('fileuploadadd', function (e, data) {console.log('add')});
    $('#image_crop_go_now').unbind('click');
    $('#image_crop_go_now').click(function(){ cropImage(id, options)});
    $('#image_crop_cancel').click(function () {
        $('#selected_image').val('');
        $('#image_crop_go_now').addClass('hidden');
        $('#image_crop_cancel').addClass('hidden');
        $('#image_upload_tabs a:first').tab('show');
    });
    // $('#'+id+'_image_crop span').click(initJCrop_{{id}});
    // $('#'+id+'_image_crop_go_cancel').click(destroyJCrop);
}

function destroyImageManager(){
    $('#image_upload_file').fileupload('destroy');
    destroyJCrop();
    $('#image_crop_go_now').unbind('click');
    $('#image_preview').html('<p>Please select or upload an image</p>');
    $('#image_crop_cancel').addClass('hidden');
}

var api;
var c;

function updateCoords(coords){
    c = coords;
}

function initJCrop(id, options){
    if(api){
        api.destroy();
    }
    $('#image_preview img').remove();
    $('#image_preview').html('<img src="'+options.uploadConfig.webDir + $('#selected_image').val() + '" id="image_preview_image"/>');
    $('#image_preview img').load(function(){


        $('#image_preview img').Jcrop({
            // start off with jcrop-light class
            bgOpacity: 0.8,
            bgColor: 'white',
            addClass: 'jcrop-dark',
            aspectRatio: options.cropConfig.aspectRatio ? options.cropConfig.minWidth/options.cropConfig.minHeight : false ,
            minSize: [ options.cropConfig.minWidth, options.cropConfig.minHeight ],
            boxWidth: 600,
            boxHeight: 400,
            onSelect: updateCoords
        },function(){
            api = this;
            api.setOptions({ bgFade: true });
            api.ui.selection.addClass('jcrop-selection');
        });

        var previewImage = $('#image_preview_image');
        if ((previewImage.width() / previewImage.height()) >= (options.cropConfig.minWidth / options.cropConfig.minHeight)) {
            var selectionWidth = parseInt(previewImage.height() / (options.cropConfig.minHeight / options.cropConfig.minWidth));
            var selectionHeight = previewImage.height();
        } else {
            var selectionWidth = previewImage.width();
            var selectionHeight = parseInt(previewImage.width() / (options.cropConfig.minWidth / options.cropConfig.minHeight));
        }
        console.log(parseInt((previewImage.width() - selectionWidth)/2),
            parseInt((previewImage.height() - selectionHeight)/2),
            selectionWidth,
            selectionHeight);

        api.setSelect([
            parseInt((previewImage.width() - selectionWidth)/2),
            parseInt((previewImage.height() - selectionHeight)/2),
            selectionWidth,
            selectionHeight
        ]);
        $('#image_crop_go_now').removeClass('hidden');
        $('#image_crop_cancel').removeClass('hidden');
        $('#image_upload_tabs a:last').tab('show');
    });
}

function destroyJCrop(){
    if(!api){
        return false;
    }
    api.destroy();
    // $('#upload_image_crop').removeClass('hidden');
    $('#upload_image_crop_go').addClass('hidden');
}


function cropImage(id, options){
    $.ajax({
        url: Routing.generate('trendix_image_crop'),
        type: 'POST',
        data: {
            'config': JSON.stringify(options),
            'imageName': $('#selected_image').val(),
            'params': {
                'x': c.x,
                'y': c.y,
                'w': c.w,
                'h': c.h
            }
        },
        success: function(data){
            var filename = data.filename;
            var previewSrc = data.previewSrc;
            $('#gallery_preview_' + id + ' .crop-error').addClass('hidden');
            $('#image_preview_image_' + id + ' .crop-error').addClass('hidden');
            if(data.error) {
                var containerID;
                if(typeof galleries[id] != 'undefined') {
                    containerID = '#gallery_preview_' + id;
                } else {
                    containerID = '#image_preview_image_'+id;
                }
                destroyJCrop(id);
                $(containerID).find('.crop-error[data-target="' + data.error + '"]').removeClass('hidden');
            } else if(typeof galleries[id] != 'undefined'){
                addImageToGallery(data.originalFile, id, data.previewSrc, options);
            }
            else{
                $('#'+id).val(filename);
                $('#image_preview_image_'+id).html('<img src="'+previewSrc+'" id="'+id+'_preview"/>');
                $('#image_preview_image_'+id+' img').css('cursor: hand; cursor: pointer;');
                $('#image_preview_image_'+id+' img').click(function(e){
                    if($( event.target ).is( "img" )){
                        $('<div class="modal hide fade"><img src="'+options.uploadConfig.webDir + $('#selected_image').val()+'"/></div>').modal();
                        return false;
                    }
                });
                $('#image_preview_'+id).removeClass('hide-disabled');
            }

            destroyJCrop(id);
            $('#selected_image').val('');
            $('#image_preview').html('<p>Please select or upload an image</p>');
            $('#image_crop_go_now').addClass('hidden');
            $('#image_crop_cancel').addClass('hidden');
            $('#image_upload_tabs a:first').tab('show');
            $('#image_upload_modal').modal('hide');
        }
    });
}

function addImageToGallery(filename, id, thumb, options)
{
    console.log('add #gallery_preview_'+id+' input');
    var nb = $('#gallery_preview_'+id+' > .gallery-image-container').length;
    var name = $('#gallery_preview_'+id).data('name');
    $('#gallery_preview_'+id).append('<div class="gallery-image-container" data-image="'+filename+'">' +
        '<span class="remove-image"><i class="glyphicon glyphicon-remove"></i></span>' +
        '<span class="gallery-image-helper"></span>' +
        '<input type="hidden" id="'+id+'_'+nb+'" name="'+name+'['+nb+']" value="'+filename+'">' +
        '<img src="' + thumb + '"/>' +
        '</div>');
    rebindGalleryRemove(options);
}

function removeImageFromGallery(filename, id, parentId, options)
{

    // ADD DELETE FILE HERE !
    $('#'+id).parent().remove();
    reorderItems(parentId, options);

}

function reorderItems(id, options)
{
    var name = $('#'+id).data('name');
    $( '#'+id+' .gallery-image-container' ).each(function(i, item){
        $(item).find('input').attr('name', name+'['+i+']');
    });
}

function rebindGalleryRemove(options)
{
    $('.gallery-image-container span').unbind('click');
    $('.gallery-image-container span').click(function(){
        removeImageFromGallery($(this).parent().data('image'), $(this).parent().find('input').attr('id'),
            $(this).closest('.gallery-well').attr('id'), options);
        return false;
    });
}