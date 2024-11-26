<?php
use Cake\Core\Configure;
?>
<?php echo $this->element('email/tableHead'); ?>
    <tbody>
        <?php echo $this->element('email/greeting'); ?>
        <tr>
            <td>
                <p>
                    Der Status deines Aktivitätsnachweises wurde geändert auf:<br />
                    <?php echo $funding->activity_proof_status_human_readable; ?>
                </p>
                <?php if ($funding->activity_proof_comment != '') { ?>
                    <p>
                        Kommentar: <b><?php echo $funding->activity_proof_comment; ?></b>
                    </p>
                <?php } ?>
                <p>
                    <?php
                        echo $this->MyHtml->link(
                            'Link zum Förderantrag',
                            Configure::read('AppConfig.serverName') . $this->MyHtml->urlFundingsEdit($funding->workshop->uid),
                        );
                    ?>
                </p>
            </td>
        </tr>
    </tbody>
<?php echo $this->element('email/tableFoot'); ?>
