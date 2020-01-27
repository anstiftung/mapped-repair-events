<?php

namespace App\Test\TestCase\Traits;

use Cake\ORM\TableRegistry;

trait LoginTrait
{
    
    protected function loginAsOrga()
    {
        $orgaUserEmail = 'johndoe@mailinator.com';
        $this->User = TableRegistry::getTableLocator()->get('Users');
        $user = $this->User->find('all', [
            'conditions' => [
                'Users.email' => $orgaUserEmail
            ],
            'contain' => [
                'Groups'
            ]
        ])->first();
        $user->revertPrivatizeData();
        
        $this->session([
            'Auth' => [
                'User' => $user->toArray()
            ]
        ]);
        
    }

}
?>