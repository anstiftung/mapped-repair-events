<?php

namespace App\Test\TestCase\Traits;

use Cake\Datasource\FactoryLocator;

trait LoginTrait
{

    protected function loginAsOrga()
    {
        $userEmail = 'johndoe@mailinator.com';
        $this->User = FactoryLocator::get('Table')->get('Users');
        $user = $this->User->find('all',
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
        $this->User = FactoryLocator::get('Table')->get('Users');
        $user = $this->User->find('all',
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
        $this->User = FactoryLocator::get('Table')->get('Users');
        $user = $this->User->find('all',
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