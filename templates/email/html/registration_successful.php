<?php
declare(strict_types=1);
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
                    Bitte klicke auf folgenden Link, um deine E-Mail-Adresse zu bestätigen:
                    <a href="<?php echo Configure::read('AppConfig.serverName') . '/users/activate/' . $data['Users']['confirm'];?>">
                        <?php echo Configure::read('AppConfig.serverName') . '/users/activate/' . $data['Users']['confirm']; ?>
                    </a>
                </p>
                <p>
                    Vielen Dank für Dein Engagement!
                </p>
            </td>
        </tr>
    </tbody>
<?php echo $this->element('email/tableFoot'); ?>
