<?php
declare(strict_types=1);
?>
<?php echo $this->element('email/tableHead'); ?>
    <tbody>
        <tr>
            <td>
                <p>
                    Ein User möchte gelöscht werden:<br />
                    UID: <?php echo  $loggedUser->uid; ?><br />
                    Name: <?php echo $loggedUser->name; ?><br />
                    Nick: <?php echo $loggedUser->nick;?><br />
                </p>
                <p>
                <?php
                    if ($deleteMessage != '') {
                        echo 'Grund:' . '<br />';
                        echo $deleteMessage;
                    } else {
                        echo 'Der User hat keinen Grund angegeben.';
                    }
                    ?>
                </p>
                <p>
                    Beim Löschen Überprüfung, ob der User der/die letzte Organisator*in bei Initiativen ist, nicht vergessen!
                </p>
            </td>
        </tr>
    </tbody>
<?php echo $this->element('email/tableFoot'); ?>
