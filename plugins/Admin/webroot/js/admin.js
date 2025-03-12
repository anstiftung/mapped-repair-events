
MappedRepairEvents.Admin = {

    init : function() {
        MappedRepairEvents.Helper.highlightFormFields();
        MappedRepairEvents.Helper.bindFlashMessageCancelButton();
        MappedRepairEvents.Helper.beautifyDropdowns();
        MappedRepairEvents.RowMarker.init();
    },

    bindDelete: function(deleteMethod) {
        $('a.delete-link').click(function() {
            var linkedButton = $(this);
            var id = $(this).attr('id').replace('delete-link-', '');
            var objectType = $(this).attr('data-object-type');
            $.prompt(
                'Möchtest du dieses Objekt wirklich löschen? ID ' + id
                ,{
                    buttons: {L\u00f6schen: true, Abbrechen: false},
                    submit: function(v,m,f) {
                        if(m) {
                            MappedRepairEvents.Admin.deleteAppObject(linkedButton, id, deleteMethod, objectType);
                        }
                    }
                }
            );
        });
    },

    deleteAppObject : function(linkedButton, id, deleteMethod, objectType) {
        MappedRepairEvents.Helper.ajaxCall(
            deleteMethod,
            {
                id: id,
                object_type: objectType,
            },
            {
                onOk : function(data) {
                    linkedButton.closest('tr').animate( { opacity: 'toggle'}, 'fast', function() {});
                },
                onError : function(data) {
                    alert(data.msg);
                }
            }
        );

    }

};
