<?php
use Cake\Core\Configure;

if (!$object->is_submitted) {
    return;
}

echo '<div>';
    if ($object->money_transfer_date !== null) {
        echo $object->money_transfer_date->i18nFormat(Configure::read('DateFormat.de.DateShort'));
    }
echo '</div>';
