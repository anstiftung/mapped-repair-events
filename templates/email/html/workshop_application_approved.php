<?php
declare(strict_types=1);
use Cake\Core\Configure;
?>
<?php echo $this->element('email/tableHead'); ?>
    <tbody>
        <?php echo $this->element('email/greeting', ['data' => $userEntity]); ?>
        <tr>
            <td>
                <p>
                    deine Anfrage zur Mitarbeit bei der Initiative "<?php echo $workshop->name; ?>" wurde soeben bestätigt.
                </p>
                <p>
                    Hier gelangst du zur Profil-Seite der Initiative:<br />
                    <?php echo Configure::read('AppConfig.serverName') . $this->MyHtml->urlWorkshopDetail($workshop->url); ?>
                </p>
            </td>
        </tr>
    </tbody>
<?php echo $this->element('email/tableFoot'); ?>
