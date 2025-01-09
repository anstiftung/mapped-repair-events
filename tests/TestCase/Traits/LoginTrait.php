<?php
declare(strict_types=1);

namespace App\Test\TestCase\Traits;

use Cake\Datasource\FactoryLocator;

trait LoginTrait
{

    protected function loginAsOrga()
    {
        $userEmail = 'johndoe@mailinator.com';
        $usersTable = FactoryLocator::get('Table')->get('Users');
        $user = $usersTable->find('all',
            conditions: [
                'Users.email' => $userEmail,
            ],
            contain: [
                'Groups',
            ]
        )->first();
        $user->revertPrivatizeData();
        $this->session(['Auth' => $user]);
    }

    protected function loginAsAdmin()
    {
        $userEmail = 'admin@mailinator.com';
        $usersTable = FactoryLocator::get('Table')->get('Users');
        $user = $usersTable->find('all',
            conditions: [
                'Users.email' => $userEmail,
            ],
            contain: [
                'Groups',
            ]
        )->first();
        $user->revertPrivatizeData();
        $this->session(['Auth' => $user]);
    }

    protected function loginAsRepairhelper()
    {
        $userEmail = 'maxmuster@mailinator.com';
        $usersTable = FactoryLocator::get('Table')->get('Users');
        $user = $usersTable->find('all',
            conditions: [
                'Users.email' => $userEmail,
            ],
            contain: [
                'Groups',
            ]
        )->first();
        $user->revertPrivatizeData();
        $this->session(['Auth' => $user]);
    }

}
?>