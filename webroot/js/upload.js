MappedRepairEvents.Upload = {

    /**
     * to prevent form in form, form must be insert manually at the end of the document
     */
    appendFormToBody : function(objectType, uid, imageSrc, uploadType) {

        var multiple = uploadType === 'multiple';

        var html = '<form data-uid="'+uid+'" data-object-type="'+objectType+'" id="mini-upload-form-' + uploadType + '-' + uid + '" class="mini-upload-form ' + (multiple ? 'multiple' : 'single') + ' " method="post" action="/admin/intern/ajaxMiniUploadFormTmpImageUpload/" enctype="multipart/form-data">';
        if (multiple) {
            html += '<ul class="images-container"></ul>';
            html += '<div class="sc"></div>';
        }
        html += '<div class="drop">';
        if (imageSrc != '') {
            html += '<img class="existingImage" src="'+imageSrc+'" />';
            html += '<p>Bestehendes Bild überschreiben</p>';
        } else {
            html += '<p>';
            if (multiple) {
                html += 'Neue Bilder';
            } else {
                html += 'Neues Bild';
            }
            html += ' hochladen</p>';
        }

        if (multiple) {
            html += '<p class="info-text">ACHTUNG! Die Seite wird beim Speichern neu geladen, d.h. ungespeicherte Daten gehen verloren!</b></p>';
        }

        html += '<p class="info-text">Bitte nur selbstgemachte Bilder verwenden (keine aus dem Internet).</p>';
        html += '<a class="upload-button">PC durchsuchen</a>';
        html += '<input' + (multiple ? ' multiple' : '') + ' type="file" name="upload" accept="image/jpeg, image/x-png" />';
        html += '</div>';
        html += '<ul class="uploads-container"></ul>';
        html += '</form>';

        $('body').append(html);

    },

    setImages : function(images) {
        this.images = $.parseJSON(images);
    },

    addImagesToDom : function() {
        $('.image-container').remove();
        for(var i in this.images) {
            var imageUploadForm = $('form.mini-upload-form.multiple');
            this.buildMultipleImagesContainer(imageUploadForm, this.images[i].src, this.images[i].text);
        }
    },

    updateImageCount : function(count) {
        $('.image-count').remove();
        $('.add-image-button.multiple').after(
            $('<span />').addClass('image-count').html('(' + count + ') ' + (count === 1 ? 'Bild' : 'Bilder'))
        );
    },

    buildSingleImageContainer : function(imageUploadForm, imageSrc) {
        var container = imageUploadForm.find('.drop');
        container.find('img,i').remove();
        container.prepend($('<img />').
            attr('src', imageSrc).
            addClass('uploadedFile')
        );
        var imageContainer = container;
        imageUploadForm.find('ul.uploads-container li').remove();
        imageUploadForm.find('button.deleteImage').remove();
        MappedRepairEvents.Upload.addRotateButtons(imageContainer);
        return imageContainer;
    },

    buildMultipleImagesContainer : function(imageUploadForm, imageSrc, imageText) {
        var container = imageUploadForm.find('ul.images-container');
        var imageContainer = $('<li />');
        imageContainer.addClass('image-container ui-state-default ' + MappedRepairEvents.Upload.buildUniqueContainerIdentifier(imageSrc));
        imageContainer.append($('<img />').
            attr('src', imageSrc).
            addClass('uploadedFile')
        );
        imageContainer.append($('<input />').
            attr('type', 'text').
            val(imageText)
        );
        MappedRepairEvents.Upload.addDeleteButton(
            true,
            imageContainer,
            '<p>Willst du das Bild wirklich löschen?</p>',
            function(dialog) {
                $('.ui-dialog button').attr('disabled', false);
                var imageContainer = $('.image-container.' + dialog[0].className.split(' ')[0]);
                imageContainer.remove();
                dialog.dialog('close');
            },
            '<div class="' + MappedRepairEvents.Upload.buildUniqueContainerIdentifier(imageSrc) + '"></div>'
        );
        container.append(imageContainer);
        container.sortable();
        MappedRepairEvents.Upload.addRotateButtons(imageContainer);
        return imageContainer;
    },

    buildUniqueContainerIdentifier : function(src) {
        var fileExtension = MappedRepairEvents.Helper.getFileExtension(src);
        var fileNameLength = 10;
        var end = src.length - fileExtension.length - 1;
        var start = end - fileNameLength;
        return 'img-' + src.substring(start, end);
    },

    addRotateButtons : function(imageContainer) {

        imageContainer.append('<a title="gegen den Uhrzeigersinn drehen" class="modify-icon img-rotate-acw" href="javascript:void(0);"><i class="fas fa-undo fa-border"></a>');
        imageContainer.append('<a title="im Uhrzeigersinn drehen" class="modify-icon img-rotate-cw" href="javascript:void(0);"><i class="fas fa-redo fa-border"></a>');

        imageContainer.find('.img-rotate-acw').on('click', function() {
            MappedRepairEvents.Upload.rotateImage($(this), 'CW'); //SIC
        });

        imageContainer.find('.img-rotate-cw').on('click', function() {
            MappedRepairEvents.Upload.rotateImage($(this), 'ACW'); //SIC
        });

    },

    addDeleteButton : function(isMultiple, container, deleteImageText, onDelete, dialogContainer) {

        var imageButton = $('<a title="löschen" class="modify-icon img-delete" href="javascript:void(0);"><i class="far fa-trash-alt fa-border"></a>');
        imageButton.prependTo(container);

        container.find('a.img-delete').on('click', function(e) {
            e.preventDefault();
            $(dialogContainer).appendTo('body')
                .html(deleteImageText)
                .dialog({
                    modal: true,
                    title: 'Bild löschen?',
                    autoOpen: true,
                    width: 400,
                    resizable: false,
                    buttons: {
                        'Ja': function () {
                            $('.ui-dialog .ajax-loader').show();
                            $('.ui-dialog button').attr('disabled', 'disabled');
                            onDelete($(this));
                        },'Nein': function () {
                            $(this).dialog('close');
                        }
                    },
                    close: function (event, ui) {
                        $(this).remove();
                    }
                });
        });
    },

    init : function(container, uploadType) {

        var configuration = MappedRepairEvents.AppFeatherlight.initLightboxForForms(
            function() {
                var imageUploadForm = $('.featherlight-content form');
                var saveUrl = '/admin/intern/ajaxMiniUploadFormSaveUploadedImage';

                var params = {
                    uid : imageUploadForm.data('uid'),
                    objectType: $('.featherlight-content form').data('object-type'),
                };

                var isMultiple = imageUploadForm.hasClass('multiple');
                if (isMultiple) {
                    var imageContainers = imageUploadForm.find('ul.images-container li');
                    saveUrl = '/admin/intern/ajaxMiniUploadFormSaveUploadedImagesMultiple';
                    params.files = [];
                    var i = 0;
                    imageContainers.each(function() {
                        i++;
                        params.files.push({
                            filename: MappedRepairEvents.Upload.cutRandomStringOffImageSrc($(this).find('img.uploadedFile').attr('src')),
                            rank: i,
                            text: $(this).find('input').val().trim()
                        });
                    });
                } else {
                    params.filename = MappedRepairEvents.Upload.cutRandomStringOffImageSrc(imageUploadForm.find('.drop img').attr('src'));
                }

                MappedRepairEvents.Helper.ajaxCall(
                    saveUrl
                    ,params
                    ,{ onError : function(data) {
                        alert(data.msg);
                        MappedRepairEvents.AppFeatherlight.closeLightbox();
                    }
                    ,onOk : function(data) {
                        if (isMultiple) {
                            document.location.reload();
                        } else {
                            $('input.image-field').val(data.fileNamePlainWithTimestamp);
                            var addButton = $('a.add-image-button.single');
                            addButton.find('i,img').remove();
                            var image = $('<img/>');
                            image.attr('src', data.filePathWithTimestamp);
                            addButton.append(image);
                        }
                        MappedRepairEvents.AppFeatherlight.closeLightbox();
                    }
                    });
            },
            function() {
                if (!MappedRepairEvents.Upload.images) {
                    $('button.save').attr('disabled', 'disabled');
                }
            },
            MappedRepairEvents.AppFeatherlight.closeLightbox,
            $('.mini-upload-form.' + uploadType).get(0)
        );

        $(container).featherlight(configuration);

        $(container).one('click', function() {

            if (MappedRepairEvents.Upload.images) {
                MappedRepairEvents.Upload.addImagesToDom();
            }

            var isMultiple = $(this).attr('class').indexOf('multiple') > -1;

            var imageUploadForm = $('form.mini-upload-form.' + (isMultiple ? 'multiple' : 'single'));
            var uidString = imageUploadForm.data('uid');
            var isAddButtonAvailable = $('.add-image-button.' + (isMultiple ? 'multiple' : 'single') + ' img').length > 0;

            if (!isMultiple && isAddButtonAvailable) {
                MappedRepairEvents.Upload.addDeleteButton(
                    false,
                    imageUploadForm,
                    '<p>Willst du das Bild wirklich löschen?</p><p><b>Achtung: Die Seite wird nach dem Löschen neu geladen und alle nicht gespeicherten Formular-Daten werden dadurch zurückgesetzt!</b></p>',
                    function() {
                        document.location.href = '/admin/intern/ajaxMiniUploadFormDeleteImage/' + uidString;
                    },
                    '<div></div>'
                );
            }

            var ul = imageUploadForm.find('ul.uploads-container');

            imageUploadForm.find('.drop a.upload-button').on('click', function() {
                // Simulate a click on the file input button
                // to show the file browser dialog
                $(this).parent().find('input').click();
            });

            // Initialize the jQuery File Upload plugin
            imageUploadForm.fileupload({

                // This element will accept file drag/drop uploading
                dropZone: imageUploadForm.find('.drop'),

                autoUpload: false,

                // This function is called when a file is added to the queue;
                // either via the browse button, or via drag/drop:
                add: function (e, data) {

                    var tpl = $('<li class="working"><p></p><input type="text" value="0" data-width="48" data-height="48"'+
                         ' data-fgColor="#0788a5" data-readOnly="1" data-bgColor="#3e4043" /></li><div class="sc"></div>');

                    // Append the file name and file size
                    tpl.find('p').text(data.files[0].name);

                    // Add the HTML to the UL element
                    data.context = tpl.appendTo(ul);

                    // Initialize the knob plugin
                    tpl.find('input').knob();

                    // Listen for clicks on the cancel icon
                    tpl.find('span').click(function(){

                        if(tpl.hasClass('working')){
                            jqXHR.abort();
                        }

                        tpl.fadeOut(function(){
                            tpl.remove();
                        });

                    });

                    // Automatically upload the file once it is added to the queue
                    var jqXHR = data.submit();
                },

                progress: function(e, data){

                    // Calculate the completion percentage of the upload
                    var progress = parseInt(data.loaded / data.total * 100, 10);

                    // Update the hidden input field and trigger a change
                    // so that the jQuery knob plugin knows to update the dial
                    data.context.find('input').val(progress).change();

                    if(progress == 100){
                        data.context.removeClass('working');
                    }
                },

                done: function (e, data) {

                    if (imageUploadForm.hasClass('multiple')) {
                        imageUploadForm.find('ul.uploads-container li p:contains(' + data.files[0].name + ')').parent().remove();
                    } else {
                        imageUploadForm.find('img.uploadedFile').remove();
                        imageUploadForm.find('ul.uploads-container li').remove();
                        imageUploadForm.find('.modify-icon').remove();
                    }

                    var result = JSON.parse(data.result);
                    if (result.status) {

                        var imageContainer;
                        if (imageUploadForm.hasClass('multiple')) {
                            imageContainer = MappedRepairEvents.Upload.buildMultipleImagesContainer(imageUploadForm, result.filename);
                        } else {
                            imageContainer = MappedRepairEvents.Upload.buildSingleImageContainer(imageUploadForm, result.filename);
                        }
                        $('button.save').attr('disabled', false);

                    } else {
                        imageUploadForm.find('ul.uploads-container li').remove();
                        alert(result.msg);
                    }
                },

                fail: function(e, data){
                    // Something has gone wrong!
                    data.context.addClass('error');
                }

            });

            // Prevent the default action when a file is dropped on the window
            $(document).on('drop dragover', function (e) {
                e.preventDefault();
            });

        });

    },

    cutRandomStringOffImageSrc : function(imageSrc) {
        return imageSrc.replace(/\?.{3}/g, '');
    },

    rotateImage : function(button, direction) {

        var image = button.parent().find('img.uploadedFile');
        image.css('opacity', 0.3);

        MappedRepairEvents.Helper.ajaxCall(
            '/admin/intern/ajaxMiniUploadFormRotateImage/'
            ,{filename: MappedRepairEvents.Upload.cutRandomStringOffImageSrc(image.attr('src')),
                direction: direction}
            ,{
                onOk : function(data) {
                    image.attr('src', data.rotatedImageSrc);
                    image.css('opacity', 1);
                }
                ,onError : function(data) {
                    alert(data.message);
                }
            }
        );

    },

    deactivateLightboxForm : function() {
        $('.featherlight-content .form-buttons button').attr('disabled', 'disabled');
        $('.featherlight-content .ajax-loader').css('visibility', 'visible');
    },

    activateLightboxForm : function() {
        $('.featherlight-content form-buttons button').removeAttr('disabled');
        $('.featherlight-content .ajax-loader').css('visibility', 'hidden');
    }

};