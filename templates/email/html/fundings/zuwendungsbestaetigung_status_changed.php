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
                    Der Status deiner Zuwendungsbestätigung wurde geändert auf:<br />
                    <b><?php echo $funding->zuwendungsbestaetigung_status_human_readable; ?></b>
                </p>
                <?php if ($funding->zuwendungsbestaetigung_comment != '') { ?>
                    <p>
                        Kommentar: <b><?php echo $funding->zuwendungsbestaetigung_comment; ?></b>
                    </p>
                <?php } ?>
                <p>
                    <?php
                        echo $this->MyHtml->link(
                            'Upload Zuwendungsbestätigung',
                            Configure::read('AppConfig.serverName') . $this->MyHtml->urlFundingsUploadZuwendungsbestaetigung($funding->uid),
                        );
                    ?>
                </p>
            </td>
        </tr>
    </tbody>
<?php echo $this->element('email/tableFoot'); ?>
