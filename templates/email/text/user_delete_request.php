<?php use Cake\Core\Configure; ?>
Ein User möchte gelöscht werden:

<?php echo 'UID: ' . $appAuth->getUserUid(); ?>

<?php echo 'Name: ' . $appAuth->getUsername(); ?>

<?php echo 'Nick: ' . $appAuth->getUserNick();?>

<?php 
if ($deleteMessage != '') {
    echo 'Grund:';
    echo $deleteMessage;
} else {
    echo 'Der User hat keinen Grund angegeben.';
}
?>


Beim Löschen Überprüfung, ob der User der letzte Organisator bei <?php echo Configure::read('AppConfig.initiativeNamePlural'); ?> ist, nicht vergessen!