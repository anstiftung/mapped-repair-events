<?php
declare(strict_types=1);

use Cake\Core\Configure;
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

    <?php
        if (!$this->request->getSession()->read('isMobile')) {
            echo $this->element('workshop/worknewsToggle', [
                'subscribed' => $subscribed,
                'workshop' => $workshop,
                'worknews' => $worknews,
            ]);
        }
    ?>

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

        if ($onlineEventsCount > 0) {
            echo 'Ich möchte die anstehenden Termine mit meinem digitalen Kalender synchronisieren: <a href="' . $this->Html->urlEventIcal($workshop->uid) . '">Hier klicken</a>.';
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
            echo $this->element('workshop/aboutUs', [
                'workshop' => $workshop
            ]);
            echo $this->element('workshop/team', [
                'team' => $team
            ]);
            echo $this->element('workshop/statistics', [
                'workshop' => $workshop
            ]);

            if ($this->request->getSession()->read('isMobile')) {
                echo $this->element('workshop/worknewsToggle', [
                    'subscribed' => $subscribed,
                    'workshop' => $workshop,
                    'worknews' => $worknews,
                ]);
            }
    
            if ($this->request->getSession()->read('isMobile') && count($workshop->events) > 0) {
                echo '<h2>'.__('Next Events').'</h2>';
                echo '<div id="calEvents"></div>';
                $this->element('addScript', ['script' => "
                    var calEvents = [];
                    var events = ".json_encode($workshop->events).";
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
                            '".__('{0} will be added shortly.', [Configure::read('AppConfig.categoriesNameWorkshops')])."'
                        );
                        calEvents.push(calEvent);
                    }
                    $('#calEvents').html(calEvents.join(''));".
                    JS_NAMESPACE.".Helper.bindCalEventClickWorkshopDetailMobile();
                "]);

                if ($event) {
                    $this->element('addScript', ['script' =>
                        JS_NAMESPACE.".Helper.showEventDetail('".json_encode($event)."', true);
                    "]);
                }

            }

        ?>

        </div>

        <div class="sc"></div>
    </div>

</div>
