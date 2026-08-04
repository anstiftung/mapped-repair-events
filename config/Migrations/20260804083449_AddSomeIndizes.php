<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddSomeIndizes extends BaseMigration
{

public function change(): void
    {

    $sql = "ALTER TABLE `users` ADD INDEX(`status`);
            ALTER TABLE `users` ADD INDEX(`firstname`);
            ALTER TABLE `users` ADD INDEX(`lastname`);
            ALTER TABLE `events` ADD INDEX(`status`);
            ALTER TABLE `workshops` ADD INDEX(`status`);
            ALTER TABLE `categories` ADD INDEX(`name`);
            ALTER TABLE `brands` ADD INDEX(`name`);
            ";
    $this->adapter->execute($sql);
    }
}
