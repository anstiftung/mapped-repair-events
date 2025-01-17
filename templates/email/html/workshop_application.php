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
                    <?php echo $user->firstname . ' ' . $user->lastname;?> (<?php echo $user->email; ?>) möchte bei deiner Initiative "<?php echo $workshop->name; ?>" mitmachen.
                </p>
                <p>
                    Du kannst die Anfrage hier bestätigen (dazu du musst eingeloggt sein):<br />
                    <?php echo Configure::read('AppConfig.serverName') . $this->MyHtml->urlUserWorkshopAdmin(); ?>
                </p>
            </td>
        </tr>
    </tbody>
<?php echo $this->element('email/tableFoot'); ?>
