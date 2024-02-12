MappedRepairEvents.ModalElfinder = {

    init : function(editor) {

        // if closed via dialog-closer-x it can not be opened again
        // destroy on every open
        MappedRepairEvents.AppFeatherlight.closeLightbox();

        var opts = {
            url : '/js/elfinder/php/connector.minimal.php',
            cssAutoLoad: false,
            lang: 'de',
            i18nBaseUrl: '/js/elfinder/js/i18n/',
            workerBaseUrl: '/js/elfinder/js/worker/',
            soundPath: '/js/elfinder/sounds/',
            uiOptions: {
                toolbar: [
                    ['upload', 'rm'],
                ],
            },
            
            getFileCallback : function(file, fm) {
                MappedRepairEvents.AppFeatherlight.closeLightbox();
                if (file.mime.startsWith('image/')) {
                    editor.selection.insertNode(
                        editor.create.fromHTML('<img src="' + file.url + '">')
                    );
                } else {
                    MappedRepairEvents.Helper.copyToClipboard(fm.convAbsUrl(file.url));
                    MappedRepairEvents.Helper.setFlashMessageSuccess('Die Url der hochgeladenen Datei wurde erfolgreich in deine Zwischenablage kopiert.');
                }
            }
        };

        var html = $('<div id="elfinder"></div>').elfinder(opts);
        var configuration = MappedRepairEvents.AppFeatherlight.initLightbox({html: html});
        $.featherlight(html, configuration);

    },

};