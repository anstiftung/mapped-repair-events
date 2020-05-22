MappedRepairEvents.Detect = {

    initIsMobileListener : function() {
        $(window).on('resize orientationchange', MappedRepairEvents.Helper.debounce(
            function() {
                MappedRepairEvents.Detect.setIsMobile(function() {
                    document.location.reload();
                });
            }, 200)
        );
    }

    ,setIsMobile : function(callback) {
        MappedRepairEvents.Helper.ajaxCall(
            '/detects/setIsMobile',
            {
                width:  document.body.clientWidth
            },
            {
                onOk : function(data) {
                    if (data.viewChanged && callback) {
                        callback();
                    }
                }
                ,onError : function(data) {
                    console.log(data);
                }
            }
        );
    }

};
