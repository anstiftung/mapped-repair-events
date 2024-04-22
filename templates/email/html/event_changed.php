<?php
use Cake\Core\Configure;
?>
<?php echo $this->element('email/tableHead'); ?>
    <tbody>
        <tr>
            <td>
                <p>
                    Die von dir abonnierte Initiative <b><?php echo $event->workshop->name; ?></b> hat folgenden Termin geändert: <b><?php echo $event->datumstart->i18nFormat(Configure::read('DateFormat.de.DateLong2WithWeekday')); ?></b>.
                </p>
                <p>
                    <?php if (in_array('status', $dirtyFields)) { ?>
                        - Der Termin wurde <?php echo $event->status == APP_ON ? 'aktiviert' : 'deaktiviert'; ?>.<br />
                    <?php } ?>
                    <?php if (in_array('datumstart', $dirtyFields)) { ?>
                        - Das Datum des Termins wurde von <?php echo $originalValues['datumstart']->i18nFormat(Configure::read('DateFormat.de.DateLong2WithWeekday')); ?> auf <b><?php echo $event->datumstart->i18nFormat(Configure::read('DateFormat.de.DateLong2WithWeekday')); ?></b> geändert.<br />
                    <?php } ?>
                    <?php if (in_array('uhrzeitstart', $dirtyFields) || in_array('uhrzeitend', $dirtyFields)) { ?>
                        - Neue Uhrzeit: <b><?php echo $event->uhrzeitstart->i18nFormat(Configure::read('DateFormat.de.TimeShort')); ?> - <?php echo $event->uhrzeitend->i18nFormat(Configure::read('DateFormat.de.TimeShort')); ?> Uhr</b><br />
                    <?php } ?>
                    <?php if (in_array('lat', $dirtyFields) || in_array('lng', $dirtyFields)) { ?>
                        - Neuer Veranstaltungsort: <b><?php echo $event->ort . ', ' . $event->strasse; ?><?php echo (!empty($event->veranstaltungsort)) ? ' (' . $event->veranstaltungsort . ')' : ''; ?></b><br />
                    <?php } ?>
                <p>
                    Klicke <a href="<?php echo Configure::read('AppConfig.serverName') . $this->MyHtml->urlWorkshopDetail($workshop->url);?>">hier für weitere Termine von <?php echo $workshop->name; ?></a>.
                </p>
                <p>
                    Klicke
                    <?php
                        echo $this->MyHtml->link(
                            'hier',
                            Configure::read('AppConfig.serverName').'/initiativen/newsunsub/' . $unsub,
                        );
                    ?>, um das Abonnement zu beenden und deine E-Mail-Adresse zu entfernen.
                </p>
            </td>
        </tr>
    </tbody>
<?php echo $this->element('email/tableFoot'); ?>