<?php use Cake\Core\Configure; ?>
Ein User möchte gelöscht werden:

<?php echo 'UID: ' . $loggedUser->uid; ?>

<?php echo 'Name: ' . $loggedUser->name; ?>

<?php echo 'Nick: ' . $loggedUser->nick;?>

<?php
if ($deleteMessage != '') {
    echo 'Grund:';
    echo $deleteMessage;
} else {
    echo 'Der User hat keinen Grund angegeben.';
}
?>


Beim Löschen Überprüfung, ob der User der letzte Organisator bei <?php echo Configure::read('AppConfig.initiativeNamePlural'); ?> ist, nicht vergessen!