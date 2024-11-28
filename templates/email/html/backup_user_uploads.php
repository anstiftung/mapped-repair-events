<?php

echo 'Folders:<br /><br />';

foreach($foldersToBackup as $folderToBackup) {
    echo $folderToBackup . '<br />';
}

echo '<br />Größe: ' . $size;
