<?php
declare(strict_types=1);

use App\Model\Entity\Funding;
use Cake\Core\Configure;

$this->element('addScript', ['script' =>
    JS_NAMESPACE.".Helper.bindCancelButton();".
    JS_NAMESPACE.".Helper.bindDeleteEventButton();"
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
                echo 'Bitte jene Veranstaltungen anhaken, die im Rahmen des Förderprogramms im Jahr 2025 tatsächlich durchgeführt wurden.<br />';
                echo 'Es müssen <b>mindestens ' . Funding::MIN_CONFIRMED_EVENTS . ' Veranstaltungen</b> angehakt werden.';
            echo '</p>';

            echo '<p style="margin-top: 10px;">';
                echo $this->Html->link(
                    '<i class="fa fa-plus-circle"></i> Neuen Termin erstellen',
                    Configure::read('AppConfig.htmlHelper')->urlEventNew($funding->workshop_uid),
                    [
                        'class' => 'button',
                        'escape' => false,
                    ]
                );
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
                            $eventActions = [];
                            if (!$event->datumstart->isPast()) {
                                $eventActions[] = $this->Html->link(
                                    '<i class="far fa-edit fa-border"></i>',
                                        $this->Html->urlEventEdit($event->uid),
                                    [
                                        'title' => 'Termin bearbeiten',
                                        'escape' => false,
                                    ]
                                );
                            } else {
                                $eventActions[] = $this->Html->link(
                                    '<i class="far fa-edit fa-border"></i>',
                                        'javascript:void(0);',
                                    [
                                        'title' => 'Vergangener Termin - Bearbeiten nicht mehr möglich',
                                        'class' => 'disabled',
                                        'escape' => false,
                                    ]
                                );
                            }
                            $eventActions[] = $this->Html->link(
                                '<i class="far fa-trash-alt fa-border"></i>',
                                'javascript:void(0)',
                                    [
                                        'title' => 'Termin löschen',
                                        'escape' => false,
                                        'data-event-uid' => $event->uid,
                                        'data-redirect-url' => $this->Html->urlFundingsConfirmEvents($funding->uid),
                                        'class' => 'delete-event',
                                        'style' => 'margin-left: 5px;',
                                    ]
                            );
                            echo '<div class="event-info">' . implode(' / ', $eventInfos) . '</div>';
                            echo '<div class="event-actions">' . implode('', $eventActions) . '</div>';
                        echo '</div>';
                    }
                    $i++;

                    echo '<br />';

                    echo $this->element('cancelAndSaveButton', [
                        'saveLabel' => 'Speichern',
                    ]);

                echo '</div>';


            echo $this->Form->end();

        ?>

    </div>
</div>