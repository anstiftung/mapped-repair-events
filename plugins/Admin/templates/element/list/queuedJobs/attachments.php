<?php
$data = json_decode($object->data);

if (empty($data->settings->attachments)) {
    return;
}

foreach($data->settings->attachments as $name => $attachment) {
    echo '<p>' . $name . '<p>';
}