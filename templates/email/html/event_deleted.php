<?php
declare(strict_types=1);
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
                    Hier kommst du <a href="<?php echo Configure::read('AppConfig.serverName') . $this->MyHtml->urlWorkshopDetail($event->workshop->url);?>">zum Profil von <?php echo $event->workshop->name; ?></a>.
                </p>
                <p>
                    Um deine E-Mail-Adresse aus der Abonnementliste für diese Initiative zu entfernen, klicke <?php echo $this->MyHtml->link('hier', Configure::read('AppConfig.serverName').'/initiativen/newsunsub/' . $unsub);?>.
                </p>
            </td>
        </tr>
    </tbody>
<?php echo $this->element('email/tableFoot'); ?>