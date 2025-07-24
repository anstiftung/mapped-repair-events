<?php
declare(strict_types=1);

use App\Model\Entity\Funding;

$this->element('addScript', ['script' =>
    JS_NAMESPACE.".Helper.bindCancelButton();"
]);
echo $this->element('jqueryTabsWithoutAjax', [
    'links' => $this->Html->getUserBackendNaviLinks($loggedUser->uid, true, $loggedUser->isOrga()),
    'selected' => $this->Html->urlFundings(),
]);
?>

<div class="profile ui-tabs custom-ui-tabs ui-widget-content">
    <div class="ui-tabs-panel">
        <?php
            echo $this->element('heading', ['first' => $metaTags['title']]);

            echo '<p style="margin-top: 20px;">';
                echo 'Bitte jene Veranstaltungen anhaken, die im Rahmen des Förderprogramms tatsächlich durchgeführt wurden.<br />';
                echo 'Es müssen <b>mindestens ' . Funding::MIN_CONFIRMED_EVENTS . ' Veranstaltungen</b> bestätigt werden.';
            echo '</p>';

            echo $this->Form->create($funding, [
                'novalidate' => 'novalidate',
                'url' => $this->Html->urlFundingsConfirmEvents($funding->uid),
                'id' => 'fundingForm',
            ]);

                echo $this->Form->hidden('referer', ['value' => $referer]);
                $this->Form->unlockField('referer');

                echo '<div class="events-wrapper">';
                    $i = 0;
                    foreach($funding->workshop->funding_all_future_events as $event) {
                        echo '<div class="event">';
                            echo $this->Form->checkbox('confirmedevents[]', [
                                'value' => $event->uid,
                                'label' => 'Bestätigen',
                                'checked' => !empty($event->fundingconfirmedevent),
                            ]);
                            $eventInfos = [
                                $event->datumstart->format('d.m.Y'),
                                $event->uhrzeitstart_formatted . '-' . $event->uhrzeitend_formatted . ' Uhr',
                                $event->ort,
                                'UID: ' . $event->uid,
                            ];
                            if (!$event->datumstart->isPast()) {
                                $eventInfos[] = $this->Html->link(
                                    '<i class="far fa-edit fa-border"></i>',
                                        $this->Html->urlEventEdit($event->uid),
                                    ['title' => 'Termin bearbeiten', 'escape' => false]
                                );
                            }
                            echo '<div class="event-info">' . implode(' / ', $eventInfos) . '</div>';
                        echo '</div>';
                    }
                    $i++;
                echo '</div>';

                echo $this->element('cancelAndSaveButton', [
                    'saveLabel' => 'Speichern',
                ]);

            echo $this->Form->end();

        ?>

    </div>
</div>