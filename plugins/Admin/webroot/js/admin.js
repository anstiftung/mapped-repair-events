
MappedRepairEvents.Admin = {

    init : function() {
        MappedRepairEvents.Helper.bindFlashMessageCancelButton();
        MappedRepairEvents.Helper.beautifyDropdowns();
        new RowMarker().init()
    },

    bindDelete: function(deleteMethod) {
        $('a.delete-link').click(function() {
            var linkedButton = $(this);
            var id = $(this).attr('id').replace('delete-link-', '');
            var objectType = $(this).attr('data-object-type');
            Swal.fire({
                animation: false,
                html: 'Möchtest du dieses Objekt wirklich löschen? ID ' + id,
                showCancelButton: true,
                showCloseButton: true,
                confirmButtonText: 'L\u00f6schen',
                cancelButtonText: 'Abbrechen',
            }).then(function(result) {
                if (result.isConfirmed) {
                    MappedRepairEvents.Admin.deleteAppObject(linkedButton, id, deleteMethod, objectType);
                }
            });
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
