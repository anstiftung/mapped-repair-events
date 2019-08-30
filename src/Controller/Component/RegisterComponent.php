<?php
namespace App\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;

class RegisterComponent extends AppComponent
{

    public $User;

    public $components = [
        'AppFlash',
        'FluxBb'
    ];

    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        parent::__construct($registry, $config);
        $this->User = TableRegistry::getTableLocator()->get('Users');
    }

    /**
     *
     * @return $user
     */
    public function register($user)
    {
        $user['Users']['confirm'] = md5(StringComponent::createRandomString());
        $user['Users']['status'] = APP_OFF;
        $user['Users']['private'] = $this->User->getDefaultPrivateFields();
        
        $userEntity = $this->User->newEntity($user, ['validate' => 'Registration']);
        $result = $this->User->save($userEntity);
        
        if (! isset($user['Users']['password'])) {
            $password = $this->User->setNewPassword($result->uid);
        } else {
            // only from unit test
            $password = $user['Users']['password'];
            $userPassword = [
                'password' => $user['Users']['password']
            ];
            $userEntity = $this->User->patchEntity($result, $userPassword);
            $result = $this->User->save($userEntity);
        }

        $email = new Email('default');
        $email->viewBuilder()->setTemplate('registration_successful');
        $email->setSubject('Deine Registrierung bei '. Configure::read('AppConfig.htmlHelper')->getHostName())
            ->setViewVars([
            'password' => $password,
            'data' => $user
        ]);
        
        if (Configure::read('debug')) {
            $email->setTo(Configure::read('AppConfig.debugMailAddress'));
        } else {
            $email->setTo($user['Users']['email']);
        }
        
        if ($email->send()) {
            $this->AppFlash->setFlashMessage('Deine Registrierung war erfolgreich. Bitte überprüfe dein E-Mail-Konto um deine E-Mail-Adresse zu bestätigen.');
        }
        
        return $userEntity;
    }
}

?>