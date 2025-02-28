<?php

echo $object->zip . ' ' . $object->city;
if (!empty($object->province)) {
    echo ' (' . $object->province->name . ')';
}