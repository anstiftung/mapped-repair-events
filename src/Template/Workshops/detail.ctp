<?php
if ($this->request->getSession()->read('isMobile')) {
    $this->element('addScript', ['script' => 
        JS_NAMESPACE.".MobileFrontend.adaptWorkshopDetail();
    "]);
};
if (!$this->request->getSession()->read('isMobile')) {
    $this->element('addScript', ['script' => 
        JS_NAMESPACE.".Helper.initWorkshopDetail(".$workshop->uid.");
    "]);
}
?>

<div id="workshop-detail" class="map-detail">

	<div class="workshop-detail-header">
        <?php
            echo $this->element('highlightNavi', [
                'main' => ''
            ]);
            
            echo '<div style="float:left;">';
                echo $this->element('heading', [
                    'first' => $workshop->name
                ]);
            echo '</div>';
            if ($hasModifyPermissions) {
                echo '<a class="btn edit" href="'.$this->Html->urlWorkshopEdit($workshop->uid).'">
                        <button class="btn" type="submit" >
                        ' . __('Edit Repair Initiative') . '
                        </button></a>';
            }
        ?>
    </div>

	<div class="sc"></div>

    <div class="left">
    
    	<div id="datum"></div>
    	<div class="widget-link events">
    		<a title="<?php echo __('Events on your website'); ?>" href="/widgets/integration/#1"><?php echo __('Events on your website'); ?></a>
    	</div>

        <?php
        if (!$this->request->getSession()->read('isMobile')) {
            echo $this->element('calendar', [
                'wuid' => $workshop->uid,
                'event' => $event,
                'hasModifyPermissions' => $hasModifyPermissions
            ]);
        }
        
        if ($hasModifyPermissions) { ?>
            <a class="btn add-event-link" href="/termine/add/<?php echo $workshop->uid; ?>">
            	<button class="btn" type="submit">
            		<?php echo __('Add a new event'); ?>
            	</button>
            </a>
        <?php }

        echo '<div class="sc"></div>';
        if (!$subscribed) {
            echo $this->Form->create($worknews, [
                'novalidate' => 'novalidate'
            ]);
            
                echo $this->Form->hidden('Worknews.workshop_uid', [
                    'value' => $workshop->uid
                ]);
                
                echo '<span style="float:left;margin:10px 0px;width:100%;">Ich möchte über anstehende Reparaturtermine dieser Initiative per E-Mail informiert werden.</span>';
                echo $this->Form->control('Worknews.email', [
                    'type' => 'email',
                    'label' => false,
                    'style' => 'float:left; margin:0px 10px 0px 0px;'
                ]);
                echo '<div class="submit"><input type="submit" value="'.__('Submit').'"></div>';
            
            echo $this->Form->end();
        }
        
        echo '<div class="sc"></div><br>';
        if ($subscribed) {
            echo '<div>Deine E-Mail-Adresse <strong>(', $appAuth->getUserEmail(), ')</strong> ', __('is already subscribed to news from this workshop.'), ' <a href="/initiativen/newsunsub/', $worknews->unsub, '">', __('Click here to unsubscribe'), '.</a></div>';
        }
        ?>
        
        </div>
        <br />

        <div class="right">
        	<div id="tabs" class="ui-tabs-nav" style="background:none; margin-top:-25px;">
        		<ul>
        			<li><a href="#tabs-1"><?php echo __('WHO WE ARE'); ?></a></li>
        			<?php if (count($team) > 1) { ?>
        				<li><a href="#tabs-2"><?php echo __('TEAM'); ?></a></li>
        			<?php } ?>
        			<?php if ($showStatistics) { ?>
        				<li><a href="#tabs-3">STATISTIK</a></li>
        			<?php } ?>
        		</ul>
        
        <?php
            echo $this->element('workshopTabs/aboutUs', [
                'workshop' => $workshop
            ]);
            echo $this->element('workshopTabs/team', [
                'team' => $team
            ]);
            if ($showStatistics) {
                echo $this->element('workshopTabs/statistics', [
                    'workshop' => $workshop
                ]);
            }
            
            if ($this->request->getSession()->read('isMobile') && count($workshop->events) > 0) {
                echo '<h2>'.__('Next Events').'</h2>';
                echo '<div id="calEvents"></div>';
                $this->element('addScript', ['script' => "
                    var calEvents = [];
                    var events = ".json_encode($workshop->events, true).";
                    for(var i=0;i<events.length;i++) {
                    	events[i].hasModifyPermissions = '".$hasModifyPermissions."';
                        var calEvent = ".JS_NAMESPACE.".Helper.getCalEventHtml(
                            events[i],
                            ".$workshop->uid.",
                            true,
                            '".__('This event is online')."',
                            '".__('This event is offline')."',
                            '".__('Edit this event')."',
                            '".__('Duplicate this event')."',
                            '".__('Are you sure you want to delete this event?')."',
                            '".__('Delete this event')."',
                            '".__('Skills will be added shortly.')."'
                        );
                        calEvents.push(calEvent);
                    }
                    $('#calEvents').html(calEvents.join(''));".
                    JS_NAMESPACE.".Helper.bindCalEventClickWorkshopDetailMobile();
                "]);
            
                if ($event) {
                    $this->element('addScript', ['script' => 
                        JS_NAMESPACE.".Helper.showEventDetail('".json_encode($event, true)."', true);
            		"]);
            	}
            
            }
            
        ?>
        
        </div>

        <div class="sc"></div>
    </div>
        
</div>
