<?php
use Cake\Core\Configure;
?>
<?php echo $this->element('email/tableHead'); ?>
    <tbody>
        <?php echo $this->element('email/greeting'); ?>
        <tr>
            <td>
                <p>
                    Dein Förderantrag wurde erfolgreich eingereicht und bewilligt.
                </p>
                <p>
                    <?php
                        echo $this->MyHtml->link(
                            'Download Förderlogo BMUV',
                            Configure::read('AppConfig.serverName') . '/files/foerderung/Foerderlogo-BMUV.zip',
                        );
                    ?>
                </p>
            </td>
        </tr>
    </tbody>
<?php echo $this->element('email/tableFoot'); ?>
