<?php
use Cake\Core\Configure;
?>
<?php echo $this->element('email/tableHead'); ?>
    <tbody>
        <tr>
            <td>
                <p>
                    Die von dir abonnierte Initiative </b><?php echo $workshop->name; ?></b> hat nächste Woche einen Termin:<br />
                </p>
                <p>
                    Datum: <b><?php echo $event->datumstart->i18nFormat(Configure::read('DateFormat.de.DateLong2WithWeekday')); ?></b><br />
                    Uhrzeit: <b><?php echo $event->uhrzeitstart->i18nFormat(Configure::read('DateFormat.de.TimeShort')) . ' - ' . $event->uhrzeitend->i18nFormat(Configure::read('DateFormat.de.TimeShort')) . ' Uhr'; ?></b><br />
                    Veranstaltungsort: <?php echo $event->ort . ', ' . $event->strasse; ?><?php echo (!empty($event->veranstaltungsort)) ? ' (' . $event->veranstaltungsort . ')' : ''; ?>
                </p>

                <?php
                    if ($event->is_online_event) {
                        echo '<p><b>Der Termin findet online statt.</b></p>';
                    }
                ?>

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