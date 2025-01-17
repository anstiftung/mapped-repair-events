<?php
declare(strict_types=1);

namespace App\Test\Fixture;

class UsersFixture extends AppFixture
{

      public array $records = [
          [
              'uid' => 1,
              'firstname' => 'John',
              'lastname' => 'Doe',
              'nick' => 'JohnDoe',
              'email' => 'johndoe@mailinator.com',
              'additional_contact' => 'my-additional@email.com',
              'zip' => '66546',
              'street' => 'Test Street 4',
              'phone' => '055466554645',
              'status' => APP_ON,
              'private' => 'firstname,lastname,email,additional-contact,zip,street,phone',
              'created' => '2019-09-17 08:23:23',
              'modified' => '2019-09-17 08:23:23'
          ],
          [
              'uid' => 3,
              'firstname' => 'Max',
              'lastname' => 'Muster',
              'nick' => 'MaxMuster',
              'email' => 'maxmuster@mailinator.com',
              'additional_contact' => 'my-additional@email.com',
              'zip' => '66546',
              'street' => 'Test Street 4',
              'phone' => '055466554645',
              'status' => APP_ON,
              'private' => '',
              'created' => '2019-09-17 08:23:23',
              'modified' => '2019-09-17 08:23:23'
          ],
          [
              'uid' => 8,
              'firstname' => 'Admin',
              'lastname' => 'Admin',
              'nick' => 'Admin',
              'email' => 'admin@mailinator.com',
              'additional_contact' => '',
              'zip' => '66546',
              'street' => 'Test Street 4',
              'phone' => '055466554645',
              'status' => APP_ON,
              'private' => '',
              'created' => '2019-09-17 08:23:23',
              'modified' => '2019-09-17 08:23:23'
          ],
      ];
}
?>