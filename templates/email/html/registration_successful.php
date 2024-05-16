<?php
use Cake\Core\Configure;
?>
<?php echo $this->element('email/tableHead'); ?>
    <tbody>
        <?php echo $this->element('email/greeting', ['data' => (object) $data['Users']]); ?>
        <tr>
            <td>
                <p>
                    vielen Dank für deine Anmeldung bei <?php echo $this->MyHtml->getHostName(); ?>.
                </p>
                <p>
                    Dein vorläufiges Passwort lautet:<br />
                    <b><?php echo $password; ?></b>
                </p>
                <p>
                    Bitte melde dich über den folgenden Link auf der Plattform an, um deine E-Mail-Adresse zu bestätigen:
                    <?php echo Configure::read('AppConfig.serverName'); ?>/users/activate/<?php echo $data['Users']['confirm']; ?>
                </p>
                <p>
                    Vielen Dank für Dein Engagement!
                </p>
            </td>
        </tr>
    </tbody>
<?php echo $this->element('email/tableFoot'); ?>
