<?php

    $wuid = !empty($wuid) ? $wuid : '';
    $isHomeCalendar = $wuid == '';
    empty($event) and $event = false;
    
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
<?php
if (!$this->request->getSession()->read('isMobile')) {
        echo $this->element('covid19Banner'); 
    }
?>

<script type="text/javascript">
var autoClicked = false;

function initCal(){
	
	$('#calendar').fullCalendar({
		header : {
		   left: 'prev' // ' today'
		  ,center: 'title'
		  ,right: 'next' // 'month,basicWeek,basicDay'
		}
		,lang : 'de'
		<?php if ( $event ): ?>,defaultDate : '<?php echo $event[1]; ?>'<?php endif; ?>
		,viewRender : function() {

		    <?php if (!$this->request->getSession()->read('isMobile')) { ?>
		    	$('#covid-19-banner').animate({ opacity: 'toggle' }, 'slow');
		  	<?php } ?>
		    
			$('#selectedDate').attr('data-date', '').html('');
			$('#calEvents').hide();
					
			var waitForCal = setInterval(function() {
				
				if ($('.fc-day').length) {

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
					
					$('.fc-day, .fc-day-number').on('click', function() {

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
						
						$('.fc-day, .fc-day-number').removeClass('selected');

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

						$('#selectedDate').attr('data-date', date).html('<?php echo __('Events on'); ?> ' +  MappedRepairEvents.Helper.niceDate(date));

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
							$('.fc-content-skeleton td.fc-today').addClass('hover');
						  }, function() {
							$(this).removeClass('hover');
							$('.fc-content-skeleton td.fc-today').removeClass('hover');
						  }
  					);

				}
				
			}, 500);
		}
	});
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
    			$('td.fc-day[data-date='+ev.datumstart_formatted+']').addClass('fc-has-event '+( ev.status == 1 ? 'online' : 'offline' ));
    				
    			var calEvent = MappedRepairEvents.Helper.getCalEventHtml(
    				ev,
    				'<?php echo $wuid; ?>',
    				false,
    				'<?php echo __('This event is online'); ?>',
    				'<?php echo __('This event is offline'); ?>',
    				'<?php echo __('Edit this event'); ?>',
    				'<?php echo __('Duplicate this event'); ?>',
    				'<?php echo __('Are you sure you want to delete this event?'); ?>',
    				'<?php echo __('Delete this event'); ?>',
    				'<?php echo __('Skills will be added shortly.'); ?>'
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
		if (!autoClicked){
			MappedRepairEvents.Helper.showEventDetail('<?php echo json_encode($event, true); ?>', false);
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
