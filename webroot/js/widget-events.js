MappedRepairEvents.WidgetEvents = {

    loadEventsForWorkshop : function(workshopUid, num) {

        $.ajax('/workshops/ajaxGetAllWorkshopsForMap?workshopUid=' + workshopUid).done(
            function(data) {
                var eventHtml = '';
                if (data.workshops[0] && data.workshops[0].Workshop.Events) {
                    var currentDate = new Date().toJSON().slice(0,10);
                    var j = 0;
                    for(var i=0;i < data.workshops[0].Workshop.Events.length;i++) {
                        var event = data.workshops[0].Workshop.Events[i];
                        if (event.datumstart_formatted >= currentDate) {
                            j++;
                            eventHtml += '<div class="item">';
                            eventHtml += '<div onclick="window.open(\'' + window.location.protocol + '//' + window.location.hostname + event.directurl +'\', \'_blank\');">';
                            eventHtml += '<div class="namedate truncate">' + MappedRepairEvents.Helper.niceDate(event.datumstart_formatted) + ' | ' + event.eventname + '</div>';
                            eventHtml += '<div class="truncate">' + event.eventbeschreibung + '</div>';
                            eventHtml += '</div>';
                            eventHtml += '</div>';
                        }
                    }
                } else {
                    eventHtml = 'Es wurden leider keine Reparaturtermine gefunden.';
                }
                $('#items').html(eventHtml);
                $('#title').append(' (' + j + ')');
                MappedRepairEvents.WidgetEvents.initNavigation(j, num);
            }
        );

    },

    initNavigation : function(itemCount, num) {

        if (itemCount > num) {

            $('#next').show();
            $('#next').on('click', function() {
                $('#prev').show();
                var lastActiveItem = $('.item:visible:last').index();
                $('.item').hide();
                var offset = lastActiveItem + 1;
                $('.item').slice(offset, num + offset).show();
                if (lastActiveItem + num >= itemCount - 1 ) {
                    $('#next').hide();
                }
            });
            $('#prev').on('click', function() {
                $('#next').show();
                var firstActiveItem = $('.item:visible:first').index();
                $('.item').hide();
                $('.item').slice(firstActiveItem - num, firstActiveItem).show();
                if (firstActiveItem > 0 && firstActiveItem <= num) {
                    $('#prev').hide();
                }
            });

        }

        $('.item').slice(0, num).show();
    }

};
