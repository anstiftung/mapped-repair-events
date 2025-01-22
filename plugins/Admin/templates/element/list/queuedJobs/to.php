<?php
declare(strict_types=1);
$data = json_decode($object->data);

foreach($data->settings->to as $email => $name) {
    echo $email .'<br />';
}

