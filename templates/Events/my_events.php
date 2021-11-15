<?php
use Cake\Core\Configure;
echo $this->element('jqueryTabsWithoutAjax', [
    'links' => $this->Html->getUserBackendNaviLinks($appAuth->getUserUid(), true, $appAuth->isOrga())
]);
$this->element('addScript', array('script' =>
    JS_NAMESPACE.".Helper.bindToggleLinks(false, true);".
    JS_NAMESPACE.".Helper.bindToggleLinksForSubtables();".
    JS_NAMESPACE.".Helper.bindDeleteEventButton();".
    JS_NAMESPACE.".Helper.bindDownloadInfoSheetButton();".
    JS_NAMESPACE.".Helper.bindDeleteInfoSheetButton();"
));
?>

<div class="profile ui-tabs custom-ui-tabs ui-widget-content">
    <div class="ui-tabs-panel">
        <?php echo $this->element('heading', ['first' => $metaTags['title']]); ?>

        <p><br /><b>Erstelle und verwalte deine Reparaturtermine.</b><br />Vergangene Termine werden ausgegraut angezeigt, offline Termine sind durchgestrichen.<br /><br /></p>

        <?php
        $i = 0;
        foreach($workshops as $workshop) {

            $workshopRowClass = [];
            $workshopTitleSuffix = '';
            if ($workshop->status == APP_OFF) {
                $workshopTitleSuffix = ' - Initiative offline';
                $workshopRowClass[] = 'offline';
            }
            ?>

            <?php if (count($workshop->events) == 0) { ?>
                <h2 class="<?php echo join(' ', $workshopRowClass); ?>"><?php echo $workshop->name . ' (' . count($workshop->events) . ' Termine)' . $workshopTitleSuffix; ?></h2>
            <?php } else { ?>
                <a class="toggle-link heading <?php echo join(' ', $workshopRowClass); ?>" href="javascript:void(0);">
                    <i class="fa fa-plus"></i> <?php echo $workshop->name . ' (' . count($workshop->events) . ' Termin' . (count($workshop->events) == 1 ? '' : 'e') . ', ' . $this->Number->precision($workshop->infoSheetCount, 0) . ' Laufzettel)' . $workshopTitleSuffix; ?>
                </a>
            <?php } ?>

            <a href="<?php echo Configure::read('AppConfig.htmlHelper')->urlEventNew($workshop->uid); ?>" class="button add-event<?php echo $hasEditEventPermissions ? '': ' hide'; ?>"><i class="fa fa-plus-circle"></i> Neuen Termin erstellen</a>

            <?php if (count($workshop->events) > 0) { ?>

                <div class="workshop-content-wrapper">

                    <?php
                      if ($workshop->infoSheetCount > 0 && $hasEditEventPermissions) {
                        echo $this->Html->link(
                            '<i class="fa fa-download"></i> Statistik-Download',
                            'javascript:void(0);',
                            [
                                'class' => 'download-info-sheets button',
                                'escape' => false,
                                'data-workshop-uid' => $workshop->uid,
                            ]
                        );
                        echo '<div class="info-sheets-year-wrapper">';
                         echo $this->Form->year('info-sheets-year', ['empty' => 'Gesamt', 'min' => 2010, 'max' => date('Y')]);
                        echo '</div>';
                      } ?>

                    <table class="list">

                        <tr>
                            <th>Laufzettel</th>
                            <th>Id</th>
                            <th>Status</th>
                            <th>Datum, Uhrzeit</th>
                            <th>Ort</th>
                            <th>Erstellt am</th>
                            <th>Zuletzt bearbeitet</th>
                            <?php if ($hasEditEventPermissions) { ?>
                                <th class="icon">löschen</th>
                                <th class="icon">duplizieren</th>
                                <th class="icon">bearbeiten</th>
                            <?php } ?>
                            <th class="icon">anzeigen</th>
                        </tr>

                        <?php foreach($workshop->events as $event) { ?>
                            <?php
                                $rowClass = [];
                                if ($event->datumstart->isPast()) {
                                    $rowClass[] = 'deactivated';
                                }
                                if ($event->status == APP_OFF) {
                                    $rowClass[] = 'offline';
                                }
                            ?>
                            <tr class="<?php echo join(' ', $rowClass); ?>">

                                <?php
                                    echo '<td>';

                                        if (count($event->info_sheets) > 0) {
                                            echo '<a class="toggle-link-for-subtable" href="javascript:void(0);">';
                                                echo '<i class="fa fa-plus"></i>' . ' (' . count($event->info_sheets) . ')';
                                            echo '</a>';
                                        }

                                        echo $this->Html->link(
                                           '<i class="far fa-calendar-plus fa-border"></i>',
                                           $this->Html->urlInfoSheetNew($event->uid),
                                              ['title' => 'Neuen Laufzettel erstellen', 'escape' => false, 'class' => 'add-info-sheet']
                                           );

                                    echo '</td>';
                                ?>

                                <td class="eventUid"><?php echo $event->uid; ?></td>
                                <td><?php
                                    $status = Configure::read('AppConfig.status');
                                    echo $status[$event->status];
                                ?></td>
                                <td><?php
                                   echo $event->datumstart->i18nFormat(Configure::read('DateFormat.de.DateLong2')) . ', ';
                                   echo $event->uhrzeitstart->i18nFormat(Configure::read('DateFormat.de.TimeShort')) . ' - ' . $event->uhrzeitend->i18nFormat(Configure::read('DateFormat.de.TimeShort')) . ' Uhr';
                                 ?></td>
                                <td><?php echo $event->ort . ', ' . $event->strasse; ?></td>
                                <td><?php
                                if ($event->created) {
                                    echo $event->created->i18nFormat(Configure::read('DateFormat.de.DateNTimeShort'));
                                } ?></td>
                                <td><?php
                                if ($event->updated) {
                                    echo $event->updated->i18nFormat(Configure::read('DateFormat.de.DateNTimeShort'));
                                } ?></td>

                                <?php if ($hasEditEventPermissions) { ?>

                                    <?php
                                        echo '<td class="icon">';
                                        echo $this->Html->link(
                                               '<i class="far fa-trash-alt fa-border"></i>',
                                               'javascript:void(0)',
                                                  [
                                                      'title' => 'Termin löschen',
                                                      'escape' => false,
                                                      'class' => 'delete-event'
                                                  ]
                                            );
                                        echo '</td>';
                                    ?>
                                    <?php
                                        echo '<td class="icon">';
                                        echo $this->Html->link(
                                               '<i class="far fa-copy fa-border"></i>',
                                               $this->Html->urlEventDuplicate($event->uid),
                                                  ['title' => 'Termin duplizieren', 'escape' => false]
                                            );
                                        echo '</td>';
                                    ?>
                                    <?php
                                        echo '<td class="icon">';
                                        if (!$event->datumstart->isPast()) {
                                            echo $this->Html->link(
                                                   '<i class="far fa-edit fa-border"></i>',
                                                   $this->Html->urlEventEdit($event->uid),
                                                      ['title' => 'Termin bearbeiten', 'escape' => false]
                                                );
                                        }
                                        echo '</td>';
                                    ?>

                                <?php } ?>

                                <?php
                                    echo '<td class="icon">';
                                        if ($workshop->status == APP_ON && $event->status == APP_ON && !$event->datumstart->isPast()) {
                                            echo $this->Html->link(
                                                '<i class="fas fa-arrow-right fa-border"></i>',
                                                $this->Html->urlEventDetail($workshop->url, $event->uid, $event->datumstart),
                                                ['title' => 'Termin anzeigen', 'escape' => false]
                                            );
                                        }
                                    echo '</td>';
                                ?>
                            </tr>
                            <?php
                                if (!empty($event->info_sheets)) {
                                    echo '<tr class="subtable-container">';
                                        echo '<td colspan='.$infoSheetColspan.'>';
                                            echo '<table class="list info-sheet">';
                                                echo $this->element('infoSheet/infoSheetTableHeader', [
                                                ]);
                                                foreach($event->info_sheets as $info_sheet) {
                                                     echo $this->element('infoSheet/infoSheetTableRow', [
                                                         'info_sheet' => $info_sheet
                                                     ]);
                                                }
                                            echo '</table>';
                                        echo '</td>';
                                    echo '</tr>';
                                }
                            }
                      ?>
                        </table>

                </div>

            <?php } ?>

            <?php
            $i++;
            if ($i < $workshops->count()) {
                echo '<div class="dotted-line"></div>';
            }
            ?>

        <?php } ?>

        <?php
            echo $this->element('pagination');
        ?>

    </div>
</div>