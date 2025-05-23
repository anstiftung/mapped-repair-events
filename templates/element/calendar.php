<?php
declare(strict_types=1);

use Cake\Core\Configure;

    $wuid = $wuid ?? '';
    $isHomeCalendar = $wuid == '';    
    $event = $event ?? false;

    $this->element('addScript', ['script' => 'initCal();']);

    if ($this->request->getSession()->read('isMobile')) {
        $this->element('addScript', ['script' =>
            JS_NAMESPACE.".MobileFrontend.disableHoverOnSelector('td.fc-day');
        "]);
    }

?>
<div id="calendar"></div>
<div id="selectedDate"></div>
<div id="calDottedLine" class="dotted-line-full-width"></div>
<div id="calEvents"></div>

<script type="text/javascript">
var autoClicked = false;

function initCal(){

    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar : {
           left: 'prev',
           center: 'title',
           right: 'next'
        },
        <?php if ( $event ): ?>initialDate : '<?php echo $event[1]; ?>',<?php endif; ?>
        locale: 'de',
        contentHeight: 340
    });

    calendar.setOption('datesSet', function(dateInfo) {

        $('#selectedDate').attr('data-date', '').html('');
        $('#calEvents').hide();

        var waitForCal = setInterval(function() {

            if ($('.fc-day').length == 0) {
                return;
            }

            clearInterval(waitForCal);

            var date = new Date();
            var findEventsBox = $('.find-events-box');

            // add hover-effect for mouseover calendar day in right top corner
            $('.fc-day-number').hover(function() {
                var date = $(this).data('date');
                $('.fc-day').each(function() {
                    if ($(this).attr('data-date') == date) {
                        $(this).addClass('hover');
                    }
                });
            }, function() {
                $('.fc-day').removeClass('hover');
            });

            $('.fc-day').off('click').on('click', function() {

                // do not show empty box
                if (!$(this).hasClass('fc-has-event')) {
                    return false;
                }

                if ($('#calEvents').css('display') == 'block' && $(this).hasClass('selected')) {
                    MappedRepairEvents.Helper.hideAndResetCalendarEventsBox();
                    $(this).removeClass('selected');
                    <?php if (!$this->request->getSession()->read('isMobile')) { ?>
                        findEventsBox.hide();
                    <?php } ?>
                    return false;
                }

                $('#calEvents').show();

                $('.fc-day').removeClass('selected');

                if ($(this).hasClass('selected')) {
                    <?php if (!$this->request->getSession()->read('isMobile')) { ?>
                        findEventsBox.hide();
                    <?php } ?>
                    return false;
                }

                $(this).addClass('selected');

                var date = $(this).attr('data-date');

                $('.eventBox').hide();
                $('#calDottedLine').show();

                $('#selectedDate').attr('data-date', date).html('<?php echo Configure::read('AppConfig.specialEventNamePlural') . ' am '; ?> ' +  MappedRepairEvents.Helper.niceDate(date));

                $('.calEvent').each(function() {
                    var display = 'none';
                    if ($(this).data('date') == date && !$(this).hasClass('isntInRadius')) {
                        display = 'block';
                    }
                    var elementToToggle = $(this);
                    // workshop detail
                    if ($(this).next().attr('class') == 'eventBox') {
                        var elementToToggle = $(this).next();
                    }
                    elementToToggle.css({display: display});
                });

                <?php if (!$this->request->getSession()->read('isMobile')) { ?>
                    findEventsBox.css('height', $('#calEvents').height());
                    findEventsBox.show();
                <?php } ?>

            });

            getEvents();

            $('td.fc-day:not(.no-hover)').hover(
                function() {
                    $(this).addClass('hover');
                  }, function() {
                    $(this).removeClass('hover');
                }
            );

        }, 500);

    });

    calendar.render();

}

function getEvents() {

    // MappedRepairEvents.Helper.workshop for workshops.detail
    // MappedRepairEvents.MapObject.objects for workshops.home / workshops.all
    var workshops = MappedRepairEvents.MapObject && MappedRepairEvents.MapObject.objects ? MappedRepairEvents.MapObject.objects : MappedRepairEvents.Helper.workshop;

    var calEvents = [];

    for(var j in workshops) {
        if (workshops[j].Workshop.Events) {
            for (i in workshops[j].Workshop.Events) {

                var ev = workshops[j].Workshop.Events[i];
                $('td.fc-day[data-date='+ev.datumstart_formatted+']')
                    .addClass('fc-has-event '
                        + (ev.status == 1 ? 'active' : 'inactive')
                        + (ev.is_online_event == 1 ? ' fc-has-online-event' : '')
                    );

                var calEvent = MappedRepairEvents.Helper.getCalEventHtml(
                    ev,
                    '<?php echo $wuid; ?>',
                    false,
                    '<?php echo __('This event is active'); ?>',
                    '<?php echo __('This event is inactive'); ?>',
                    '<?php echo __('Edit this event'); ?>',
                    '<?php echo __('Duplicate this event'); ?>',
                    '<?php echo __('Are you sure you want to delete this event?'); ?>',
                    '<?php echo __('Delete this event'); ?>',
                    '<?php echo __('{0} will be added shortly.', [Configure::read('AppConfig.categoriesNameWorkshops')]); ?>'
                );
                calEvents.push(calEvent);
            }
        }
    }

    $('#calEvents').html(calEvents.join(''));

    <?php if ($isHomeCalendar) { ?>
        $('td.fc-day').each(function() {
            MappedRepairEvents.Helper.updateDayEventCount($(this));
        });
        MappedRepairEvents.Helper.bindCalEventClickHome();
        // automatically select current day
        // $('td.fc-today').trigger('click', false);
    <?php } ?>

    <?php if ($event): ?>
        if (!autoClicked) {
            MappedRepairEvents.Helper.showEventDetail('<?php echo json_encode($event); ?>', false);
            autoClicked = true;
        }
    <?php endif; ?>

    <?php
    /**
     * filters the available events to the events that are visible in the map (it can be zoomed in)
     * more performant implementation would be only requesting those by passing lat/lng of bounding box to request url
     */ ?>
    if (MappedRepairEvents.MapObject) {
        MappedRepairEvents.MapObject.updateCalendar();
    }

}
</script>
