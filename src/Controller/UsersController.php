<?php

namespace App\Controller;

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Mailer\Email;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

class UsersController extends AppController
{
    
    public function __construct($request = null, $response = null)
    {
        parent::__construct($request, $response);
        $this->User = TableRegistry::getTableLocator()->get('Users');
    }
    
    public function isAuthorized($user)
    {
        switch($this->request->getParam('action')) {
            case 'passwortAendern':
            case 'profil':
                return $this->AppAuth->user();
                break;
            case 'add':
                return $this->AppAuth->isAdmin();
                break;
        }
        
        return parent::isAuthorized($user);
        
    }
    
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->AppAuth->allow([
            'neuesPasswortAnfordern',
            'forum',
            'login',
            'register',
            'registerRepairhelper',
            'registerOrga',
            'activate',
            'publicProfile',
            'all'
        ]);
        
    }
    
    public function all()
    {
        
        $skillId = 0;
        if (isset($this->getRequest()->getParam('pass')[0])) {
            $skillId = (int) $this->getRequest()->getParam('pass')[0];
            $this->Skill = TableRegistry::getTableLocator()->get('Skills');
            $skill = $this->Skill->find('all', [
                'conditions' => [
                    'Skills.id' => $skillId,
                    'Skills.status' => APP_ON
                ]
            ])->first();
                
            if (empty($skill)) {
                throw new NotFoundException('skill not found');
            }
            
            $this->set('skill', $skill);
        }
        
        $conditions = [
            'Users.status' => APP_ON
        ];
        
        if ($this->getRequest()->getQuery('zip') != '') {
            $conditions['LEFT(Users.zip, 1) = '] = (int) $this->getRequest()->getQuery('zip');
            $conditions['FIND_IN_SET("zip", "Users.private") = '] = 0;
        }
        if ($skillId > 0) {
            $conditions['FIND_IN_SET("skills", "Users.private") = '] = 0;
        }
        
        $users = $this->User->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'Skills'
            ]
        ]);
        
        if ($skillId > 0) {
            $users->matching('Skills', function(Query $q) use ($skillId) {
                return $q->where(['Skills.id' => $skillId]);
            });
        }
        
        $users = $this->paginate($users, [
            'sortWhitelist' => [
                'Users.created', 'Users.nick'
            ],
            'order' => [
                'Users.created' => 'DESC'
            ]
        ]);
        $this->set('users', $users);
        
        if ($skillId > 0) {
            $correctSlug = Configure::read('AppConfig.htmlHelper')->urlSkillDetail($skillId, $skill->name);
            if ($correctSlug != Configure::read('AppConfig.htmlHelper')->urlSkillDetail($skillId, StringComponent::removeIdFromSlug($this->getRequest()->getParam('pass')[0]))) {
                $this->redirect($correctSlug);
                return;
            }
        }
        
        $this->Skill = TableRegistry::getTableLocator()->get('Skills');
        $skillsForDropdown = $this->Skill->getForDropdown(false);
        $this->set('skillsForDropdown', $skillsForDropdown);
        
        $metaTags = [
            'title' => 'Aktive'
        ];
        $this->set('metaTags', $metaTags);
    }
    
    public function publicProfile()
    {
        
        $userUid = $this->request->getParam('pass')[0];
        
        $workshopsAssociation = $this->User->getAssociation('Workshops');
        $workshopsAssociation->setConditions([
            'UsersWorkshops.approved <> \'0000-00-00 00:00:00\'',
            'Workshops.status > ' . APP_DELETED
        ]);
        $user = $this->User->find('all', [
            'conditions' => [
                'Users.uid' => $userUid,
                'Users.status > ' . APP_DELETED
            ],
            'contain' => [
                'Groups',
                'Categories',
                'Skills',
                'Workshops' => [
                    'fields' => [
                        'Workshops.url',
                        'Workshops.name',
                        'UsersWorkshops.user_uid'
                    ]
                ]
            ]
        ])->first();
        
        if (empty($user)) {
            throw new NotFoundException('user not found');
        }
        $this->set('user', $user);
        
        $metaTags = [
            'title' => ($user->firstname . $user->lastname != '' ? 'Profil von ' . $user->name : 'Profil')
        ];
        $this->set('metaTags', $metaTags);
        
    }
    
    public function welcome() {
        $this->Page = TableRegistry::getTableLocator()->get('Pages');
        $homepageIntrotext = $this->Page->getPageByName('homepage.introtext');
        $this->set('homepageIntrotext', $homepageIntrotext);
        $metaTags = [
            'title' => 'Herzlich Willkommen'
        ];
        $this->set('metaTags', $metaTags);
        
    }
    
    public function neuesPasswortAnfordern()
    {
        $metaTags = [
            'title' => 'Neues Passwort anfordern'
        ];
        $this->set('metaTags', $metaTags);
        
        $user = $this->User->newEntity();
        
        if (! empty($this->request->getData())) {
            
            $user = $this->User->newEntity($this->request->getData(), [
                'validate' => 'RequestPassword'
            ]);
            
            if (!($user->hasErrors())) {
                
                $user = $this->User->find('all', [
                    'conditions' => [
                        'Users.email' => $this->request->getData('Users.email')
                    ]
                ]) ->first();
                $user->revertPrivatizeData();
                
                $newPassword = $this->User->setNewPassword($user->uid);
                
                // send email
                $email = new Email('default');
                $email->viewBuilder()->setTemplate('new_password_request');
                $email->setSubject('Neues Passwort für '. Configure::read('AppConfig.htmlHelper')->getHostName())
                ->setViewVars([
                    'password' => $newPassword,
                    'user' => $user
                ]);
                
                if (Configure::read('debug')) {
                    $email->setTo(Configure::read('AppConfig.debugMailAddress'));
                } else {
                    $email->setTo($this->request->getData('Users.email'));
                }
                if ($email->send()) {
                    $this->AppFlash->setFlashMessage('Dir wurde ein neues Passwort zugeschickt.');
                } else {
                    $this->AppFlash->setFlashError('Das Versenden des neuen Passwortes ist fehlgeschlagen.');
                }
                
                $this->set('password', $newPassword);
                $this->set('user', $user);
                
                $this->redirect(Configure::read('AppConfig.htmlHelper')->urlLogin());
            } else {
                $this->AppFlash->setFlashError('Es sind Fehler aufgetreten.');
            }
        }
        
        $this->set('user', $user);
        
    }
    
    public function add()
    {
        $user = $this->User->newEntity(
            [
                'private' => $this->User->getDefaultPrivateFields()
            ],
            ['validate' => false]
            );
        
        $this->_profil($user, false, false);
        
        $metaTags = [
            'title' => 'Neuen User anlegen'
        ];
        $this->set('metaTags', $metaTags);
        
        $this->render('profil');
        
    }
    
    private function _profil($user, $isMyProfile, $isEditMode)
    {
        $this->Category = TableRegistry::getTableLocator()->get('Categories');
        $this->set('categories', $this->Category->getForDropdown(APP_ON));
        
        $this->Country = TableRegistry::getTableLocator()->get('Countries');
        $this->set('countries', $this->Country->getForDropdown());
        
        $this->set('groups', Configure::read('AppConfig.htmlHelper')->getUserGroupsForUserEdit($this->AppAuth->isAdmin()));
        
        $this->setReferer();
        if (empty($this->request->getData())) {
            $user->private_as_array = explode(',', $user->private);
            $this->request = $this->request->withParsedBody($user);
        } else {
            if (!empty($this->request->getData('Users.private_as_array'))) {
                $private = implode(',', $this->request->getData('Users.private_as_array'));
            } else {
                $private = [];
            }
            $this->request = $this->request->withData('Users.private', $private);
            
            // START save user generated skills
            $skills = $this->request->getData('Users.skills._ids');
            if (!empty($skills)) {
                $this->Skill = TableRegistry::getTableLocator()->get('Skills');
                $skillsToAdd = [];
                foreach($skills as $key => $skill) {
                    if (!is_numeric($skill)) {
                        unset($skills[$key]);
                        $skillsToAdd[] = $this->Skill->newEntity([
                            'name' => $skill,
                            'status' => $this->AppAuth->isAdmin() ? APP_ON : APP_OFF,
                            'owner' => $this->AppAuth->getUserUid()
                        ]);
                    }
                }
                $this->request = $this->request->withData('Users.skills._ids', $skills);
                
                $addedSkillIds = [];
                if (!empty($skillsToAdd)) {
                    $addedSkills = $this->Skill->saveMany($skillsToAdd);
                    foreach($addedSkills as $addedSkill) {
                        $addedSkillIds[] = $addedSkill->id;
                    }
                    $this->request = $this->request->withData('Users.skills._ids', array_merge($skills, $addedSkillIds));
                }
            }
            // END save user generated skills
            
            $user = $this->User->patchEntity($user, $this->request->getData(), ['validate' => 'UserEdit' . ($this->AppAuth->isAdmin() ? 'Admin' : 'User')]);
            $errors = $user->getErrors();
            if (empty($errors)) {
                $this->User->save($user);
                
                // update own profile
                if ($isMyProfile) {
                    $this->AppAuth->setUser($user->toArray()); // to array is important for forum to avoid __PHP_Incomplete_Class Object
                }
                
                if (Configure::read('AppConfig.fluxBbForumEnabled')) {
                    $this->FluxBb->changeUserData($user);
                    $this->FluxBb->changeUserGroup($user, $this->request->getData('Users.groups._ids'));
                }
                
                if ($isEditMode) {
                    $message = 'Das Profil wurde erfolgreich gespeichert.';
                } else {
                    $message = 'Der User wurde erfolgreich erstellt.';
                }
                $this->AppFlash->setFlashMessage($message);
                $this->redirect($this->request->getData('referer'));
            } else {
                $this->AppFlash->setFlashError(__('An error occurred while saving the form. Please check your form.'));
            }
        }
        $this->set('user', $user);
        
        $this->Page = TableRegistry::getTableLocator()->get('Pages');
        $orgaInfotext = $this->Page->getPageByName('Was.ist.ein.Organisator');
        $this->set('orgaInfotext', $orgaInfotext->text);
        $repairhelperInfotext = $this->Page->getPageByName('Was.ist.ein.Reparateur');
        $this->set('repairhelperInfotext', $repairhelperInfotext->text);
        
        $this->set('isMyProfile', $isMyProfile);
        $this->set('isEditMode', $isEditMode);
        
        
    }
    
    public function profil($userUid=null)
    {
        
        if ($userUid === null && !$this->AppAuth->user()) {
            throw new NotFoundException('not logged in and no userUid passed');
        }
        
        if ($userUid !== null && !$this->AppAuth->isAdmin()) {
            throw new NotFoundException('only admins can change other profiles');
        }
        
        // own profile
        $isMyProfile = false;
        if ($userUid === null && $this->AppAuth->user()) {
            $userUid = $this->AppAuth->getUserUid();
            $metaTags = [
                'title' => 'Mein Profil'
            ];
            $isMyProfile = true;
        }
        
        $user = $this->User->find('all', [
            'conditions' => [
                'Users.uid' => $userUid,
                'Users.status > ' . APP_DELETED
            ],
            'contain' => [
                'Groups',
                'Categories',
                'Skills'
            ]
        ])->first();
        $user->revertPrivatizeData();
        
        if (empty($user)) {
            throw new NotFoundException('user not found');
        }
        
        $this->Skill = TableRegistry::getTableLocator()->get('Skills');
        $skillsForDropdown = $this->Skill->getForDropdown(true);
        $this->set('skillsForDropdown', $skillsForDropdown);
        
        $this->_profil($user, $isMyProfile, true);
        
        // profile from other user
        if (!$isMyProfile && $this->AppAuth->isAdmin()) {
            $metaTags = [
                'title' => 'Profil von ' . $user->name
            ];
        }
        $this->set('metaTags', $metaTags);
        
    }
    
    public function passwortAendern()
    {
        
        $metaTags = [
            'title' => 'Passwort ändern'
        ];
        $this->set('metaTags', $metaTags);
        
        $user = $this->User->newEntity();
        $this->set('user', $user);
        
        if (empty($this->request->getData())) {
            return;
        }
        
        $user = $this->User->newEntity();
        $this->set('user', $user);
        
        $userUid = $this->AppAuth->getUserUid();
        $this->User->id = $userUid;
        
        $user = $this->User->newEntity($this->request->getData(), [
            'validate' => 'ChangePassword'
        ]);
        
        if (!($user->hasErrors())) {
            $user = $this->User->get($this->AppAuth->getUserUid());
            $user->revertPrivatizeData();
            $user2save = [
                'password' => $this->request->getData('Users.password_new_1')
            ];
            $entity = $this->User->patchEntity($user, $user2save);
            $this->User->save($entity);
            $this->AppFlash->setFlashMessage('Dein Passwort wurde erfolgreich geändert.');
            $this->redirect('/');
        }
        
        $this->set('user', $user);
    }
    
    public function forum()
    {
        if (Configure::read('AppConfig.fluxBbForumEnabled')) {
            //aufruf von /forum direkt nach login zeigt login-daten nicht an (cookie noch nicht gesetzt)
            $this->redirect(Configure::read('AppConfig.htmlHelper')->urlForum(false));
        } else {
            throw new NotFoundException();
        }
    }
    
    public function login()
    {
        $metaTags = [
            'title' => 'Anmelden'
        ];
        $this->set('metaTags', $metaTags);
        if ($this->request->is('post')) {
            $user = $this->AppAuth->identify();
            if ($user) {
                $this->AppAuth->setUser($user);
                $redirectUrl = Configure::read('AppConfig.htmlHelper')->urlUserHome();
                if ($this->AppAuth->isOrga()) {
                    $workshop = TableRegistry::getTableLocator()->get('Workshops');
                    $userWorkshops = $workshop->getWorkshopsForAssociatedUser($this->AppAuth->getUserUid(), APP_OFF);
                    if ($userWorkshops->count() == 1) {
                        $redirectUrl = Configure::read('AppConfig.htmlHelper')->urlWorkshopDetail($userWorkshops->first()->url);
                    }
                    if ($userWorkshops->count() > 1) {
                        $redirectUrl = Configure::read('AppConfig.htmlHelper')->urlUserWorkshopAdmin();
                    }
                }
                $this->redirect($redirectUrl);
            } else {
                $this->AppFlash->setFlashError('Der Login hat nicht funktioniert. Benutzername oder Passwort falsch? Konto aktiviert?');
            }
        }
    }
    
    public function delete() {
        $metaTags = [
            'title' => 'Profil löschen'
        ];
        $this->set('metaTags', $metaTags);
        
        $this->setReferer();
        
        if (!empty($this->request->getData())) {
            $email = new Email('default');
            $email->viewBuilder()->setTemplate('user_delete_request');
            $email->setTo(Configure::read('AppConfig.notificationMailAddress'))
            ->setSubject('User "'.$this->AppAuth->getUserNick().'" möchte gelöscht werden')
            ->setViewVars([
                'appAuth' => $this->AppAuth,
                'deleteMessage' => $this->request->getData('deleteMessage')
            ]);
            $email->send();
            $this->AppFlash->setFlashMessage('Deine Lösch-Anfrage wurde erfolgreich übermittelt. Wir werden dein Profil in den nächsten Tagen löschen.');
            $this->AppAuth->logout();
            $this->redirect('/');
        }
    }
    
    public function activate() {
        
        if (! isset($this->request->getParam('pass')['0'])) {
            $this->AppFlash->setFlashError(__('Invalid parameters.'));
            return;
        }
        
        $conditions = [
            'Users.status >= ' . APP_OFF
        ];
        
        $this->User = TableRegistry::getTableLocator()->get('Users');
        $user = $this->User->find('all', [
            'conditions' => array_merge($conditions, [
                'Users.confirm' => $this->request->getParam('pass')['0']
            ])
        ])->first();
        $user->revertPrivatizeData();
        
        if (empty($user)) {
            $this->AppFlash->setFlashError(__('Invalid activation code.'));
            return;
        }
        
        $user = $this->User->get($user->uid, [
            'conditions' => $conditions,
            'contain' => [
                'Categories',
                'Groups'
            ]
        ]);
        $user->revertPrivatizeData();
        $user2save = [
            'confirm' => 'ok',
            'status' => APP_ON
        ];
        $entity = $this->User->patchEntity($user, $user2save);
        $this->User->save($entity);
        
        $this->AppAuth->setUser($user->toArray()); // toArray is important for forum to avoid __PHP_Incomplete_Class Object
        
        // activate newsletter if existing
        $this->Newsletter = TableRegistry::getTableLocator()->get('Newsletters');
        $newsletter = $this->Newsletter->find('all', [
            'conditions' => [
                'Newsletters.email' => $user->email,
                'Newsletters.confirm != \'ok\''
            ]
        ])->first();
        if (!empty($newsletter)) {
            $this->loadComponent('CptNewsletter');
            $this->CptNewsletter->activateNewsletterAndSendNotification($newsletter);
        }
        
        if (Configure::read('AppConfig.fluxBbForumEnabled')) {
            $this->FluxBb->insert($user->uid, $user->nick, $user->email);
        }
        $this->AppFlash->setFlashMessage('Dein Account ist nun aktiviert, du bist eingeloggt und kannst deine Profildaten ergänzen bzw. dein Passwort ändern.');
        
        $this->redirect(Configure::read('AppConfig.htmlHelper')->urlUserHome());
        
    }
    
    public function registerRepairhelper() {
        $this->register(GROUPS_REPAIRHELPER);
        // assures rendering of success message on redirected page and NOT before and then not showing it
        if (empty($this->request->getData())) {
            $this->render('register');
        }
    }
    
    public function registerOrga() {
        $this->register(GROUPS_ORGA);
        // assures rendering of success message on redirected page and NOT before and then not showing it
        if (empty($this->request->getData())) {
            $this->render('register');
        }
    }
    
    public function register($userGroup=GROUPS_REPAIRHELPER)
    {
        
        $this->loadComponent('Register');
        
        $this->Country = TableRegistry::getTableLocator()->get('Countries');
        $this->set('countries', $this->Country->getForDropdown());
        
        $this->Category = TableRegistry::getTableLocator()->get('Categories');
        $this->set('categories', $this->Category->getForDropdown(APP_ON));
        
        $this->set('groups', Configure::read('AppConfig.htmlHelper')->getUserGroupsForRegistration());
        
        $user = $this->User->newEntity();
        
        if (! empty($this->request->getData())) {
            
            if (!empty($this->getRequest()->getData()) && ($this->getRequest()->getData('antiSpam') == '' || $this->getRequest()->getData('antiSpam') < 3)) {
                $this->AppFlash->setFlashError('S-p-a-m-!');
                $this->redirect('/');
                return;
            }
            
            if ($this->request->getData('Users.i_want_to_receive_the_newsletter')) {
                $this->loadComponent('CptNewsletter');
                $newsletter = $this->CptNewsletter->getConfirmedNewsletterForEmail($this->request->getData('Users.email'));
                // only create new newsletter if not already subscribed
                if (empty($newsletter)) {
                    $newsletter = $this->CptNewsletter->prepareEntity(
                        [
                            'email' => $this->request->getData('Users.email'),
                            'plz' => $this->request->getData('Users.zip'),
                        ]
                        );
                    $this->CptNewsletter->save($newsletter);
                }
            }
            
            $this->request = $this->request->withData('groups', ['_ids' => [$userGroup]]);
            $user = $this->User->patchEntity($user, $this->request->getData(), ['validate' => 'Registration']);
            
            if (!($user->hasErrors())) {
                $this->Register->register($this->request->getData());
                $this->redirect(Configure::read('AppConfig.htmlHelper')->urlLogin());
            } else {
                $this->AppFlash->setFlashError('Es sind Fehler aufgetreten.');
                $this->set('user', $user);
                $this->render('register');
            }
        }
        
        $this->set('user', $user);
        
        $metaTags = [
            'title' => 'Registrierung - Wähle deine Rolle: OrganisatorIn oder ReparaturhelferIn'
        ];
        $this->set('metaTags', $metaTags);
    }
    
    public function logout()
    {
        if (Configure::read('AppConfig.fluxBbForumEnabled')) {
            $this->loadComponent('FluxBb');
            $this->FluxBb->logout($this->AppAuth->getUserUid());
        }
        
        $this->AppFlash->setFlashMessage('Du hast dich erfolgreich ausgeloggt.');
        $this->redirect($this->AppAuth->logout());
    }
    
}

?>