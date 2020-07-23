
MappedRepairEvents.Admin = {

    init : function() {
        MappedRepairEvents.Helper.highlightFormFields();
        MappedRepairEvents.Helper.bindFlashMessageCancelButton();
        MappedRepairEvents.Helper.beautifyDropdowns();
    },

    bindDelete: function() {
        $('a.delete-link').click(function() {
            var linkedImage = $(this);
            var uid = $(this).attr('id').replace('delete-link-', '');
            $.prompt(
                'Möchtest du dieses Objekt wirklich löschen? UID: ' + uid
                ,{
                    buttons: {Loeschen: true, Abbrechen: false}
                    ,submit: function(v,m,f) {
                        if(m){ // gigi was v, bug
                            MappedRepairEvents.Admin.deleteAppObject(linkedImage, uid);
                        }
                    }
                }
            );
        });
    },

    deleteAppObject : function(linkedImage, uid) {

        linkedImage.parent().parent().animate( { opacity: 'toggle'}, 'slow', function() {});

        var statusType = 'status';
        var value = -1;

        MappedRepairEvents.Helper.ajaxCall(
            '/admin/' + 'intern/' + 'ajaxChangeAppObjectStatus/'
            ,{uid: uid, status_type: statusType, value: value }
            ,{ onOk : function(data) {

            }
            ,onError : function(data) {
                alert(data.message);
            }
            }
        );

    }

};
