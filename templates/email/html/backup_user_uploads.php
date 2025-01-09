<?php
declare(strict_types=1);

echo 'Folders:<br /><br />';

foreach($foldersToBackup as $folderToBackup) {
    echo $folderToBackup . '<br />';
}

echo '<br />Größe: ' . $size;
