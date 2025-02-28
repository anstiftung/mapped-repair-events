<?php

echo $object->zip . ' ' . $object->city . ', ' . $object->street;
if (!empty($object->province)) {
    echo ' (' . $object->province->name;
}
if (!empty($object->country_code)) {
    echo ', ' . $object->country_code;
}
echo ')';