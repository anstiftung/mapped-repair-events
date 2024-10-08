<?php
use Cake\Core\Configure;
?>
<?php echo $this->element('email/tableHead'); ?>
    <tbody>
        <?php echo $this->element('email/greeting', ['data' => $user]); ?>
        <tr>
            <td>
                <p>
                    vielen Dank, deine Aktivierung war erfolgreich! für deine Anmeldung bei <?php echo $this->MyHtml->getHostName(); ?>.
                </p>
                <p>
                    Dein vorläufiges Passwort lautet:<br />
                    <b><?php echo $password; ?></b>
                </p>
            </td>
        </tr>
    </tbody>
<?php echo $this->element('email/tableFoot'); ?>
