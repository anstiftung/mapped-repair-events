<?php
declare(strict_types=1);

use Cake\Core\Configure;
?>
<?= $this->element('email/tableHead') ?>
<tbody>
    <tr>
        <td>
            <p>Die von dir abonnierte Initiative <b><?= h($workshop->name) ?></b> hat folgende Termine geändert:</p>
            <ul>
                <?php foreach ($affected as $change): ?>
                    <?php $event = $change['event']; ?>
                    <li>
                        <?= h($event->datumstart->i18nFormat(Configure::read('DateFormat.de.DateLong2WithWeekday'))) ?>,
                        <?= h($event->uhrzeitstart->format('H:i')) ?>–<?= h($event->uhrzeitend->format('H:i')) ?> Uhr,
                        <?= h($event->veranstaltungsort) ?>, <?= h($event->strasse) ?>, <?= h($event->zip) ?> <?= h($event->ort) ?>
                        <?php if ((int)$event->status === APP_DELETED): ?> (gelöscht)
                        <?php elseif ((int)$event->status !== APP_ON): ?> (deaktiviert)
                        <?php endif; ?>
                        <?php if (in_array('datumstart', $change['fields'], true)): ?>
                            (bisher <?= h($change['original']['datumstart']->i18nFormat(Configure::read('DateFormat.de.DateLong2WithWeekday'))) ?>)
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p><?= $this->MyHtml->link('Zum Profil von ' . $workshop->name, Configure::read('AppConfig.serverName') . $this->MyHtml->urlWorkshopDetail($workshop->url)) ?></p>
            <p><?= $this->MyHtml->link('Abonnement beenden', Configure::read('AppConfig.serverName') . '/initiativen/newsunsub/' . $unsub) ?></p>
        </td>
    </tr>
</tbody>
<?= $this->element('email/tableFoot') ?>