<?php
declare(strict_types=1);

echo $object->zip . ' ' . $object->city;
if (!empty($object->province)) {
    echo ' (' . $object->province->name . ')';
}