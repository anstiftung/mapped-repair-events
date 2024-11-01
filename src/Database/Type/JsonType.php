<?php

namespace App\Database\Type;

use Cake\Database\Driver;
use Cake\Database\Type\BaseType;
use PDO;

/**
 * implementation needed as mariadb does not support json type and uses longtext
 * mysql 8 is fine
 */
class JsonType extends BaseType
{
    public function toPHP(mixed $value, Driver $driver): mixed
    {
        if ($value === null) {
            return null;
        }

        return json_decode($value, true);
    }

    public function marshal(mixed $value): mixed
    {
        if (is_array($value) || $value === null) {
            return $value;
        }

        return json_decode($value, true);
    }

    public function toDatabase(mixed $value, Driver $driver): mixed
    {
        return json_encode($value);
    }

    public function toStatement(mixed $value, Driver $driver): int
    {
        if ($value === null) {
            return PDO::PARAM_NULL;
        }

        return PDO::PARAM_STR;
    }
}