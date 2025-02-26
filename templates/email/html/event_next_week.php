<?php
declare(strict_types=1);
use Cake\Core\Configure;
?>
<?php echo $this->element('email/tableHead'); ?>
    <tbody>
        <tr>
            <td>
                <p>
                    Die von dir abonnierte Initiative </b><?php echo $workshop->name; ?></b> hat nächste Woche eine Veranstaltung:<br />
                </p>
                <p>
                    Datum: <b><?php echo $event->datumstart->i18nFormat(Configure::read('DateFormat.de.DateLong2WithWeekday')); ?></b><br />
                    Uhrzeit: <b><?php echo $event->uhrzeitstart->i18nFormat(Configure::read('DateFormat.de.TimeShort')) . ' - ' . $event->uhrzeitend->i18nFormat(Configure::read('DateFormat.de.TimeShort')) . ' Uhr'; ?></b><br />
                    Veranstaltungsort: <?php echo $event->ort . ', ' . $event->strasse; ?><?php echo (!empty($event->veranstaltungsort)) ? ' (' . $event->veranstaltungsort . ')' : ''; ?>
                </p>

                <?php
                    if ($event->is_online_event) {
                        echo '<p><b>Die Veranstaltung findet online statt.</b></p>';
                    }
                ?>

                <p>
                    <a href="<?php echo Configure::read('AppConfig.serverName') . $this->MyHtml->urlWorkshopDetail($workshop->url);?>">Hier</a> kannst du dich informieren, ob eine Terminvereinbarung nötig ist oder du einfach vorbeikommen kannst.
                </p>

                <p>
                    Um deine E-Mail-Adresse aus der Abonnementliste für diese Initiative zu entfernen, klicke <?php echo $this->MyHtml->link('hier', Configure::read('AppConfig.serverName').'/initiativen/newsunsub/' . $unsub);?>.
                </p>
            </td>
        </tr>
    </tbody>
<?php echo $this->element('email/tableFoot'); ?>