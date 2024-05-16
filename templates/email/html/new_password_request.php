<?php
use Cake\Core\Configure;
?>
<?php echo $this->element('email/tableHead'); ?>
    <tbody>
        <?php echo $this->element('email/greeting', ['data' => $user]); ?>
        <tr>
            <td>
                <p>
                    du hast um ein neues Passwort angefragt, hier ist es:<br />
                    <b><?php echo $password; ?></b>
                </p>
                <p>
                    Du kannst dich hier einloggen:<br />
                    <?php echo Configure::read('AppConfig.serverName') . Configure::read('AppConfig.htmlHelper')->urlLogin('/users/passwortAendern'); ?>
                </p>
            </td>
        </tr>
    </tbody>
<?php echo $this->element('email/tableFoot'); ?>
