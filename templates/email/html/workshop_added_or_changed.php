<?php
use Cake\Core\Configure;
?>
<?php echo $this->element('email/tableHead'); ?>
    <tbody>
        <?php echo $this->element('email/greeting'); ?>
        <tr>
            <td>
                <p>
                    <?php echo $username; ?> hat soeben die Initiative "<?php echo $workshop->name; ?>" <?php echo $userAction; ?>.
                </p>
                <p>
                    <?php echo Configure::read('AppConfig.serverName') . $this->MyHtml->urlWorkshopDetail($workshop->url); ?>
                </p>
            </td>
        </tr>
    </tbody>
<?php echo $this->element('email/tableFoot'); ?>
