<?php
use Cake\Core\Configure;
?>
<?php echo $this->element('email/tableHead'); ?>
    <tbody>
        <tr>
            <td>
                <p>
                    Die von dir abonnierte Initiative <b><?php echo $event->workshop->name; ?></b> hat folgenden Termin gelöscht: <b><?php echo $event->datumstart->i18nFormat(Configure::read('DateFormat.de.DateLong2WithWeekday')); ?></b>.
                </p>
                <p>
                    Klicke <a href="<?php echo Configure::read('AppConfig.serverName') . $this->MyHtml->urlWorkshopDetail($event->workshop->url);?>">hier für weitere Termine von <?php echo $event->workshop->name; ?></a>.
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