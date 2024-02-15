MappedRepairEvents.Editor = {

    getDefaultOptions: function () {
        return {
            controls: {
                ul: {
                    list: Jodit.atom({
                        default: 'Default',
                    })
                },
                ol: {
                    list: Jodit.atom({
                        default: 'Default',
                    })
                },
                paragraph: {
                    list: Jodit.atom({
                        p: 'Normal',
                        h2: 'Heading 2',
                        h3: 'Heading 3',
                    }),
                },
            },
            hidePoweredByJodit: true,
            language: 'de',
            toolbarAdaptive: false,
            showPlaceholder: false,
            showCharsCounter: false,
            showWordsCounter: false,
            showXPathInStatusbar: false,
            defaultActionOnPaste: 'insert_clear_html',
        };
    },

    getUploadButton: function() {
        var button = {
            name: 'Upload',
            tooltip: 'Datei oder Bild hochladen',
            exec: (editor) => {
                MappedRepairEvents.ModalElfinder.init(editor);
            }
        };
        return button;
    },

    init: function (name, isMobile) {

        const editor = Jodit.make('textarea#' + name, {
            ... this.getDefaultOptions(),
            width: isMobile ? '100%' : 627,
            height: 650,
            buttons: [
                'bold', 'italic', 'brush',
                '|', 'undo', 'redo', 'eraser',
                '|', 'paragraph', 'ul', 'ol', 'hr',
                '|', 'left', 'center', 'right',
                '|', 'link', 'image',
                '|', 'source',
            ],
        });

        return editor;

    },

    initWithUpload: function (name, isMobile) {

        const editor = Jodit.make('textarea#' + name, {
            ... this.getDefaultOptions(),
            width: isMobile ? '100%' : 627,
            height: 650,
            buttons: [
                'bold', 'italic', 'brush',
                '|', 'undo', 'redo', 'eraser',
                '|', 'paragraph', 'ul', 'ol', 'hr',
                '|', 'left', 'center', 'right',
                '|', 'link', 'image', this.getUploadButton(),
                '|', 'source',
            ],
        });

        return editor;

    },

};