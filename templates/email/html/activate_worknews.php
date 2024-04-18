<?php
use Cake\Core\Configure;
?>
<?php echo $this->element('email/tableHead'); ?>
    <tbody>
        <tr>
            <td>
                <p>
                    Um dein Abonnement der Termine der Initiative <b><?php echo $workshop->name; ?></b> zu aktivieren, klicke bitte auf <a href="<?php echo Configure::read('AppConfig.serverName') . '/initiativen/newsact/' . $confirmationCode; ?>">diesen Link</a>.
                </p>
                <p>
                    Um deine E-Mailadresse aus der Abonnementliste für diese Initiative zu entfernen, <a href="<?php echo Configure::read('AppConfig.serverName') . '/initiativen/newsunsub/' . $unsubscribeCode; ?>">klicke hier</a>.
                </p>
            </td>
        </tr>
    </tbody>
<?php echo $this->element('email/tableFoot'); ?>

