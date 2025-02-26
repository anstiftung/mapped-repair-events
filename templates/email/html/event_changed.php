<?php
declare(strict_types=1);
use Cake\Core\Configure;
?>
<?php echo $this->element('email/tableHead'); ?>
    <tbody>
        <tr>
            <td>
                <p>
                    Die von dir abonnierte Initiative <b><?php echo $event->workshop->name; ?></b> hat folgende Veranstaltung geändert: <b><?php echo $event->datumstart->i18nFormat(Configure::read('DateFormat.de.DateLong2WithWeekday')); ?></b>.
                </p>
                <p>
                    <?php if (in_array('status', $dirtyFields)) { ?>
                        - Die Veranstaltung wurde <?php echo $event->status == APP_ON ? 'aktiviert' : 'deaktiviert'; ?>.<br />
                    <?php } ?>
                    <?php if (in_array('datumstart', $dirtyFields)) { ?>
                        - Das Datum der Veranstaltung wurde von <?php echo $originalValues['datumstart']->i18nFormat(Configure::read('DateFormat.de.DateLong2WithWeekday')); ?> auf <b><?php echo $event->datumstart->i18nFormat(Configure::read('DateFormat.de.DateLong2WithWeekday')); ?></b> geändert.<br />
                    <?php } ?>
                    <?php if (in_array('uhrzeitstart', $dirtyFields) || in_array('uhrzeitend', $dirtyFields)) { ?>
                        - Neue Uhrzeit: <b><?php echo $event->uhrzeitstart->i18nFormat(Configure::read('DateFormat.de.TimeShort')); ?> - <?php echo $event->uhrzeitend->i18nFormat(Configure::read('DateFormat.de.TimeShort')); ?> Uhr</b><br />
                    <?php } ?>
                    <?php if (in_array('lat', $dirtyFields) || in_array('lng', $dirtyFields)) { ?>
                        - Neuer Veranstaltungsort: <b><?php echo $event->ort . ', ' . $event->strasse; ?><?php echo (!empty($event->veranstaltungsort)) ? ' (' . $event->veranstaltungsort . ')' : ''; ?></b><br />
                    <?php } ?>
                    <?php if (in_array('is_online_event', $dirtyFields)) { ?>
                        - <?php echo $event->is_online_event ? 'Die Veranstaltung findet jetzt als <b>Online-Veranstaltung</b> statt.' : 'Die Veranstaltung findet <b>nicht mehr als Online-Veranstaltung</b> statt.'; ?><br />
                    <?php } ?>
                <p>
                    Hier kommst du <a href="<?php echo Configure::read('AppConfig.serverName') . $this->MyHtml->urlWorkshopDetail($workshop->url);?>">zum Profil von <?php echo $workshop->name; ?></a>.
                </p>
                <p>
                    Um deine E-Mail-Adresse aus der Abonnementliste für diese Initiative zu entfernen, klicke <?php echo $this->MyHtml->link('hier', Configure::read('AppConfig.serverName').'/initiativen/newsunsub/' . $unsub);?>.
                </p>
            </td>
        </tr>
    </tbody>
<?php echo $this->element('email/tableFoot'); ?>