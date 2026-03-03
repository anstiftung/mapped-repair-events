<?php
declare(strict_types=1);

echo '<b>' . h($object->workshop->name) . '</b><br />';
echo 'Owner: ' . h($object->owner_user->name) . '<br />';
if ($object->fundingsupporter->name != '') {
    echo 'Träger: ' . h($object->fundingsupporter->name) . '<br />';
}