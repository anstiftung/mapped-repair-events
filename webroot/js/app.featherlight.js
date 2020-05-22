MappedRepairEvents.AppFeatherlight = {

    initLightbox: function (configuration) {

        var loading = '<i class="fas fa-circle-notch fa-spin fa-3x"></i>';

        // show loader currently only works for shop order iframe
        $.featherlight.showLoader = function () {
            $('.featherlight').removeClass('featherlight-iframe');
            $('.featherlight').addClass('featherlight-loading');
            $('.featherlight .message-container').hide();
            $('.featherlight iframe').removeClass('featherlight-inner').hide();
            $('.featherlight-close-icon').after(
                '<div class="featherlight-inner">' + loading + '</div>'
            );
        };

        $.featherlight.hideLoader = function () {
            $('.featherlight').removeClass('featherlight-loading');
            $('.featherlight').addClass('featherlight-iframe');
            $('.featherlight .message-container').show();
            $('.featherlight .featherlight-inner:not(iframe)').hide();
            $('.featherlight iframe').addClass('featherlight-inner').show();
        };

        configuration = $
            .extend(
                configuration,
                {
                    openSpeed: 0,
                    loading: loading,
                    closeIcon: '<a href="javascript:void(0)" class="btn btn-outline-light btn-close"><i class="fas fa-times-circle fa-2x"></i></a>'
                }
            );

        return configuration;

    },

    initLightboxForHref: function (container) {
        var configuration = this.initLightbox({
            afterContent : function () {
                $('.featherlight-inner').addClass('href');
                MappedRepairEvents.AppFeatherlight.setMaxHeightInner();
            }
        });
        $(container).featherlight(configuration);
    },

    initLightboxImageGallery : function(container) {
        $(container).featherlightGallery({
            filter: 'a',
            afterContent: function() {
                this.$legend = this.$legend || $('<div class="legend" />').insertAfter(this.$content);
                this.$legend.text(this.$currentTarget.find('img').attr('alt'));
            }
        });
    },

    initLightboxForImages: function (container) {

        var configuration = this.initLightbox({
            type: 'image',
            onResize: function () {
                var content = $('.featherlight-content');
                content.css('max-height', $(window).height() - 20);
                var img = content.find('img');
                img.css('height', content.height());
                content.css('width', img.width() + 10);
            }
        });

        $(container).featherlight(configuration);

    },

    addLightboxToCkeditorImages : function (selector) {
        $(selector).each(function () {
            $(this).wrap('<a class="lightbox" href="' + $(this).attr('src') + '"></a>');
        });
    },

    setMaxHeightInner : function () {
        $('.featherlight-inner').css('max-height', $('.featherlight-content').height());
    },

    initLightboxForForms: function (
        onSave,
        additionalAfterOpen,
        lightboxCloseMethod,
        formHtml
    ) {
        return this.initLightbox({

            html: formHtml,

            afterContent: function () {

                $('.featherlight-content').find('input, textarea').first().focus();

                var placeholder = $('.featherlight-inner');
                placeholder.find('div.form-buttons').remove();

                var formButtons = '';
                formButtons += '<div class="form-buttons">';
                formButtons += '<button type="button" class="btn btn-success save"><i class="fas fa-check"></i> Speichern</button>';
                formButtons += '<button type="button" class="btn btn-outline-light cancel"><i class="fas fa-times"></i> Abbrechen</button>';
                formButtons += '</div>';

                $(formButtons).appendTo(placeholder);

                placeholder.find('.btn.save').on('click', function () {
                    MappedRepairEvents.Helper.addSpinnerToButton(
                        $(this),
                        'fa-check'
                    );
                    MappedRepairEvents.Helper.disableButton($(this));
                    onSave();
                });

                placeholder.find('.btn.cancel').on('click', function () {
                    lightboxCloseMethod();
                });

                if (additionalAfterOpen) {
                    additionalAfterOpen();
                }

                MappedRepairEvents.AppFeatherlight.setMaxHeightInner();

            },

            afterClose: function () {
                lightboxCloseMethod();
            }

        });

    },

    closeLightbox: function () {
        $.featherlight.close();
    },

    closeAndReloadLightbox: function () {
        var button = $('.featherlight-inner .btn.cancel');
        MappedRepairEvents.Helper.addSpinnerToButton(button, 'fa-times');
        MappedRepairEvents.Helper.disableButton(button);
        document.location.reload();
    },

    enableSaveButton: function () {
        var button = $('.featherlight-inner .btn.save');
        MappedRepairEvents.Helper.removeSpinnerFromButton(button, 'fa-check');
        MappedRepairEvents.Helper.enableButton(button);
    },

    disableSaveButton: function () {
        var button = $('.featherlight-inner .btn.save');
        MappedRepairEvents.Helper.disableButton(button);
    },

    loadImageSrcFromDataAttribute : function () {
        var img = $('.featherlight-inner .existingImage');
        if (img.attr('src') != img.data('src')) {
            img.on('load', function () {
                $(this).removeClass('loading');
            }).attr('src', img.data('src'));
        }
    }

};