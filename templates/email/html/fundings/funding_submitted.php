<?php
declare(strict_types=1);
use Cake\Core\Configure;
?>
<?php echo $this->element('email/tableHead'); ?>
    <tbody>
        <?php echo $this->element('email/greeting'); ?>
        <tr>
            <td>
                <p>
                    Dein Förderantrag wurde erfolgreich eingereicht und bewilligt.<br />
                    Im Anhang findet ihr unsere Förderbewilligung.
                </p>
                <p>
                    Bitte denkt daran, uns innerhalb von 6 Wochen nach Erhalt der Fördersumme, eine Zuwendungsbestätigung auszustellen.
                    Auf unserer Website haben wir eine <a href="https://anstiftung.de/images/vorlage_zuwendungsbestaetigung_geldzuwendung.pdf">Formular-Vorlage</a>
                    und eine <a href="https://anstiftung.de/images/vorlage_zuwendungsbestaetigung_geldzuwendung_ausfuellhilfe.pdf">Ausfüllhilfe</a> hinterlegt.
                    Bitte füllt das Dokument entsprechend und vollständig aus und ladet es in der Projektmaske hoch.
                </p>
                <p>
                    <?php
                        echo $this->MyHtml->link(
                            'Download Förderlogo BMUV',
                            Configure::read('AppConfig.serverName') . '/files/foerderung/Foerderlogo-BMUV.zip',
                        );
                    ?>
                    (siehe VII Förderrichtlinie)
                </p>
                <p>
                    Wir freuen uns, wenn der/die Empfänger/in auch die anstiftung mit Logo als unterstützende Institution im Zusammenhang mit dem
                    geförderten Vorhaben nennt. <a href="https://anstiftung.de/downloads/category/16-stiftungslogo">Download Logo anstiftung</a>
                </p>
            </td>
        </tr>
    </tbody>
<?php echo $this->element('email/tableFoot'); ?>
