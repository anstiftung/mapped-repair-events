
MappedRepairEvents.Admin = {

    init : function() {
        MappedRepairEvents.Helper.highlightFormFields();
        MappedRepairEvents.Helper.bindFlashMessageCancelButton();
        MappedRepairEvents.Helper.beautifyDropdowns();
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
                status_type: 'status',
                object_type: objectType,
                value: -1,
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
