<?php

namespace App\Controller;

use App\Controller\Component\StringComponent;
use App\Model\Table\CategoriesTable;
use App\Model\Table\CountriesTable;
use App\Model\Table\SkillsTable;
use App\Model\Table\UsersTable;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Mailer\Mailer;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\Query;
use Gregwar\Captcha\CaptchaBuilder;
use App\Services\GeoService;

class UsersController extends AppController
{

    public CategoriesTable $Category;
    public CountriesTable $Country;
    public SkillsTable $Skill;
    public UsersTable $User;

    public function __construct($request = null, $response = null)
    {
        parent::__construct($request, $response);
        $this->User = $this->getTableLocator()->get('Users');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated([
            'neuesPasswortAnfordern',
            'login',
            'logout',
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

        // pass[0] can contain "44-tag-name" or "category-name"
        $filteredCategory = null;
        if (isset($this->getRequest()->getParam('pass')[0])) {
            $this->Category = $this->getTableLocator()->get('Categories');
            $categories = $this->Category->getMainCategoriesForFrontend();
            $categorySlug = $this->getRequest()->getParam('pass')[0];
            foreach($categories as $category) {
                if (StringComponent::slugify($category->name) == $categorySlug) {
                    $filteredCategory = $category;
                }
            }
        }
        $this->set('filteredCategoryIcon', !is_null($filteredCategory) ? $filteredCategory->icon : null);
        $this->set('filteredCategoryName', !is_null($filteredCategory) ? $filteredCategory->name : null);

        $skillId = 0;
        if (is_null($filteredCategory) && isset($this->getRequest()->getParam('pass')[0])) {
            $skillId = (int) $this->getRequest()->getParam('pass')[0];
            $this->Skill = $this->getTableLocator()->get('Skills');
            $skill = $this->Skill->find('all', conditions: [
                'Skills.id' => $skillId,
                'Skills.status' => APP_ON
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
            $conditions['FIND_IN_SET("zip", private) = '] = 0; // Users.private would be converted to users.private on prod so leave it out!
        }
        if ($skillId > 0) {
            $conditions['FIND_IN_SET("skills", private) = '] = 0; // Users.private would be converted to users.private on prod so leave it out!
        }
        if (!is_null($filteredCategory)) {
            $conditions['FIND_IN_SET("categories", private) = '] = 0; // Users.private would be converted to users.private on prod so leave it out!
        }

        $users = $this->User->find('all',
        conditions: $conditions,
        contain: [
            'Skills' => function(Query $q) {
                return $q->where(['Skills.status' => APP_ON]);
            },
            'Categories',
        ]);

        if ($skillId > 0) {
            $users->matching('Skills', function(Query $q) use ($skillId) {
                return $q->where(['Skills.id' => $skillId]);
            });
        }

        if (!is_null($filteredCategory)) {
            $users->matching('Categories', function(Query $q) use ($filteredCategory) {
                return $q->where(['Categories.id' => $filteredCategory->id]);
            });
        }

        $users = $this->paginate($users, [
            'sortableFields' => [
                'Users.created', 'Users.nick'
            ],
            'order' => [
                'Users.created' => 'DESC'
            ]
        ]);
        $this->set('users', $users);

        if ($skillId > 0) {
            /* @phpstan-ignore-next-line */
            $correctSlug = Configure::read('AppConfig.htmlHelper')->urlSkillDetail($skillId, $skill->name);
            if ($correctSlug != Configure::read('AppConfig.htmlHelper')->urlSkillDetail($skillId, StringComponent::removeIdFromSlug($this->getRequest()->getParam('pass')[0]))) {
                $this->redirect($correctSlug);
                return;
            }
        }

        $this->Skill = $this->getTableLocator()->get('Skills');
        $skillsForDropdown = $this->Skill->getForDropdownIncludingCategories(false);
        $this->set('skillsForDropdown', $skillsForDropdown);

        $metaTags = [
            'title' => 'Aktive'
        ];
        $this->set('metaTags', $metaTags);

        $overviewLink = Configure::read('AppConfig.htmlHelper')->urlUsers();
        if (preg_match('`/kenntnisse`', $this->referer())) {
            $overviewLink = Configure::read('AppConfig.htmlHelper')->urlSkills();
        }
        $this->set('overviewLink', $overviewLink);
    }

    public function publicProfile()
    {

        $userUid = $this->request->getParam('id');

        $workshopsAssociation = $this->User->getAssociation('Workshops');
        $workshopsAssociation->setConditions([
            'UsersWorkshops.approved <> \'1970-01-01 00:00:00\'',
            'Workshops.status' => APP_ON,
        ]);
        $user = $this->User->find('all',
        conditions: [
            'Users.uid' => $userUid,
            'Users.status' => APP_ON,
        ],
        contain: [
            'Groups',
            'Categories',
            'Skills' => function(Query $q) {
                return $q->where(['Skills.status' => APP_ON]);
            },
            'Workshops' => [
                'fields' => [
                    'Workshops.url',
                    'Workshops.name',
                    'UsersWorkshops.user_uid'
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
        $this->Page = $this->getTableLocator()->get('Pages');
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

        $user = $this->User->newEntity([], [
            'validate' => false,
        ]);

        if (! empty($this->request->getData())) {

            $user = $this->User->newEntity($this->request->getData(), [
                'validate' => 'RequestPassword'
            ]);

            if (!($user->hasErrors())) {

                $user = $this->User->find('all', conditions: [
                    'Users.email' => $this->request->getData('Users.email')
                ]) ->first();
                $user->revertPrivatizeData();

                $newPassword = $this->User->setNewPassword($user->uid);

                // send email
                $email = new Mailer('default');
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
                $email->send();
                $this->AppFlash->setFlashMessage('Dir wurde ein neues Passwort zugeschickt.');

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
        $this->Category = $this->getTableLocator()->get('Categories');
        $this->set('categories', $this->Category->getForDropdown(APP_ON));

        $this->Country = $this->getTableLocator()->get('Countries');
        $this->set('countries', $this->Country->getForDropdown());

        $this->set('groups', Configure::read('AppConfig.htmlHelper')->getUserGroupsForUserEdit($this->isAdmin()));

        $this->setReferer();
        if (empty($this->request->getData())) {
            $user->private_as_array = explode(',', $user->private);
            $this->request = $this->request->withParsedBody($user);
            $this->request->getSession()->delete('newSkillsProfile');
        } else {

            $associatedSkills = $this->request->getData('Users.skills._ids');
            $newSkills = $this->Skill->getNewSkillsFromRequest($associatedSkills);
            $existingSkills = $this->Skill->getExistingSkillsFromRequest($associatedSkills);
            $this->request->getSession()->write('newSkillsProfile', $newSkills);
            $this->request = $this->request->withData('Users.skills._ids', $existingSkills);

            $addressString = trim($this->request->getData('Users.zip') . ', ' . $this->request->getData('Users.city') . ', ' . $this->request->getData('Users.country_code'));
            $geoService = new GeoService();
            $coordinates = $geoService->getLatLngFromGeoCodingService($addressString);
         $this->request = $this->request->withData('Users.lat', $coordinates['lat']);
            $this->request = $this->request->withData('Users.lng', $coordinates['lng']);

            if (!empty($this->request->getData('Users.private_as_array'))) {
                $private = implode(',', $this->request->getData('Users.private_as_array'));
            } else {
                $private = '';
            }
            $this->request = $this->request->withData('Users.private', $private);

            $user = $this->User->patchEntity($user, $this->request->getData(), ['validate' => 'UserEdit' . ($this->isAdmin() ? 'Admin' : 'User')]);
            if (!$user->hasErrors()) {
                $this->User->save($user);

                // update own profile
                if ($isMyProfile) {
                    $this->Authentication->setIdentity($user);
                }

                $newSkills = $this->request->getSession()->read('newSkillsProfile');
                if (!empty($newSkills)) {
                    // save new skills
                    $addedSkillIds = $this->Skill->addSkills($newSkills, $this->loggedUser->isAdmin(), $this->loggedUser->uid);
                    // save id associations to user
                    $this->request = $this->request->withData('Users.skills._ids', array_merge($this->request->getData('Users.skills._ids'), $addedSkillIds));
                    $user = $this->User->patchEntity($user, $this->request->getData());
                    $this->User->save($user);
                    $this->request->getSession()->delete('newSkillsProfile');
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

        $this->Page = $this->getTableLocator()->get('Pages');
        $orgaInfotext = $this->Page->getPageByName('Was.ist.ein.Organisator');
        $this->set('orgaInfotext', $orgaInfotext->text);
        $repairhelperInfotext = $this->Page->getPageByName('Was.ist.ein.Reparateur');
        $this->set('repairhelperInfotext', $repairhelperInfotext->text);

        $this->set('isMyProfile', $isMyProfile);
        $this->set('isEditMode', $isEditMode);


    }

    public function profil($userUid=null)
    {

        if ($userUid === null && !$this->isLoggedIn()) {
            throw new NotFoundException('not logged in and no userUid passed');
        }

        if ($userUid !== null && !$this->isAdmin()) {
            throw new NotFoundException('only admins can change other profiles');
        }

        // own profile
        $isMyProfile = false;
        if ($userUid === null && $this->isLoggedIn()) {
            $userUid = $this->isLoggedIn() ? $this->loggedUser->uid : 0;
            $metaTags = [
                'title' => 'Mein Profil'
            ];
            $isMyProfile = true;
        }

        $user = $this->User->find('all',
        conditions: [
            'Users.uid' => $userUid,
            'Users.status > ' . APP_DELETED
        ],
        contain: [
            'Groups',
            'Categories',
            'Skills',
        ])->first();
        $user->revertPrivatizeData();

        if (empty($user)) {
            throw new NotFoundException('user not found');
        }

        $this->Skill = $this->getTableLocator()->get('Skills');
        $skillsForDropdown = $this->Skill->getForDropdown(true);
        $this->set('skillsForDropdown', $skillsForDropdown);

        $this->_profil($user, $isMyProfile, true);

        // profile from other user
        $metaTags = [
            'title' => 'Mein Profil',
        ];
        if (!$isMyProfile && $this->isAdmin()) {
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

        $user = $this->User->newEntity([]);
        $this->set('user', $user);

        if (empty($this->request->getData())) {
            return;
        }

        $user = $this->User->newEntity([]);
        $this->set('user', $user);

        $user = $this->User->newEntity($this->request->getData(), [
            'validate' => 'ChangePassword'
        ]);

        if (!($user->hasErrors())) {
            $user = $this->User->get($this->isLoggedIn() ? $this->loggedUser->uid : 0);
            $user->revertPrivatizeData();
            $user2save = [
                'password' => $this->request->getData('Users.password_new_1')
            ];
            $entity = $this->User->patchEntity($user, $user2save, ['validate' => false]);
            $this->User->save($entity);
            $this->AppFlash->setFlashMessage('Dein Passwort wurde erfolgreich geändert.');
            $this->redirect('/');
        }
        $this->set('user', $user);
    }

    public function login()
    {
        $metaTags = [
            'title' => 'Anmelden'
        ];
        $this->set('metaTags', $metaTags);
        if ($this->request->is('post')) {
            $result = $this->Authentication->getResult();
            if ($result->isValid()) {
                // Use the redirect parameter if present.
                $target = $this->Authentication->getLoginRedirect();
                if (!$target) {
                    $target = Configure::read('AppConfig.htmlHelper')->urlUserHome();
                }
                $this->redirect($target);
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
            $email = new Mailer('default');
            $email->viewBuilder()->setTemplate('user_delete_request');
            $email->setTo(Configure::read('AppConfig.notificationMailAddress'))
            ->setSubject('User "'.$this->loggedUser->nick.'" möchte gelöscht werden')
            ->setViewVars([
                'loggedUser' => $this->loggedUser,
                'deleteMessage' => $this->request->getData('deleteMessage')
            ]);
            $email->send();
            $this->AppFlash->setFlashMessage('Deine Lösch-Anfrage wurde erfolgreich übermittelt. Wir werden dein Profil in den nächsten Tagen löschen.');
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

        $this->User = $this->getTableLocator()->get('Users');
        $user = $this->User->find('all', conditions: array_merge($conditions, [
            'Users.confirm' => $this->request->getParam('pass')['0']
        ]))->first();

        if (empty($user)) {
            $this->AppFlash->setFlashError(__('Invalid activation code.'));
            return;
        }

        $user = $this->User->get($user->uid,
        conditions: $conditions,
        contain: [
            'Categories',
            'Groups'
        ]);
        $user->revertPrivatizeData();
        $user2save = [
            'confirm' => 'ok',
            'status' => APP_ON
        ];
        $entity = $this->User->patchEntity($user, $user2save, ['validate' => false]);
        $this->User->save($entity);

        $this->Authentication->setIdentity($user);
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

    private function isCalledByTestSuite()
    {
        return !empty($_SERVER['argv']) && !empty($_SERVER['argv'][0]) && preg_match('`vendor/bin/phpunit`', $_SERVER['argv'][0]);
    }

    public function register($userGroup=GROUPS_REPAIRHELPER)
    {

        $this->set('isCalledByTestSuite', $this->isCalledByTestSuite());

        if (!$this->isCalledByTestSuite()) {
            if (empty($this->request->getData()) || $this->request->getData('Users.reload_captcha')) {
                $captchaBuilder = new CaptchaBuilder();
                $this->request->getSession()->write('captchaPhrase', $captchaBuilder->getPhrase());
            } else {
                $captchaBuilder = new CaptchaBuilder($this->request->getSession()->read('captchaPhrase'));
            }
            // suppress notice: Implicit conversion from float 28.5 to int loses precision
            @$captchaBuilder->build();
            $this->set('captchaBuilder', $captchaBuilder);
        }

        $this->Country = $this->getTableLocator()->get('Countries');
        $this->set('countries', $this->Country->getForDropdown());

        $this->Category = $this->getTableLocator()->get('Categories');
        $this->set('categories', $this->Category->getForDropdown(APP_ON));

        $this->Skill = $this->getTableLocator()->get('Skills');
        $this->set('skillsForDropdown', $this->Skill->getForDropdown(false));

        $this->set('groups', Configure::read('AppConfig.htmlHelper')->getUserGroupsForRegistration());

        $metaTags = [
            'title' => 'Registrierung - Wähle deine Rolle: Organisator*in oder ' . Configure::read('AppConfig.repairHelperName')
        ];
        $this->set('metaTags', $metaTags);

        $user = $this->User->newEmptyEntity();

        if (! empty($this->request->getData())) {

            $associatedSkills = $this->request->getData('Users.skills._ids');
            $newSkills = $this->Skill->getNewSkillsFromRequest($associatedSkills);
            $existingSkills = $this->Skill->getExistingSkillsFromRequest($associatedSkills);
            $this->request->getSession()->write('newSkillsRegistration', $newSkills);
            $this->request = $this->request->withData('Users.skills._ids', $existingSkills);

            $this->request = $this->request->withData('groups', ['_ids' => [$userGroup]]);
            $user = $this->User->patchEntity($user, $this->request->getData(), ['validate' => 'Registration']);

            if (!$this->isCalledByTestSuite()) {
                $captchaBuilder->setPhrase($this->request->getSession()->read('captchaPhrase'));
                if (!$captchaBuilder->testPhrase($this->request->getData('Users.captcha'))) {
                    $user->setError('captcha', 'Das Captcha ist nicht korrekt.');
                }
                if($this->request->getData('Users.reload_captcha')) {
                    $this->request = $this->request->withData('Users.captcha', '');
                    $this->request = $this->request->withData('Users.reload_captcha', false);
                }
            }

            if (!($user->hasErrors())) {

                $user = $this->request->getData();
                $user['Users']['confirm'] = md5(StringComponent::createRandomString());
                $user['Users']['status'] = APP_OFF;
                $user['Users']['private'] = $this->User->getDefaultPrivateFields();
                $userEntity = $this->User->newEntity($user, ['validate' => 'Registration']);
                $userEntity = $this->stripTagsFromFields($userEntity, 'User');
                $result = $this->User->save($userEntity);
                $password = $this->User->setNewPassword($result->uid);

                $email = new Mailer('default');
                $email->viewBuilder()->setTemplate('registration_successful');
                $email->setSubject('Deine Registrierung bei '. Configure::read('AppConfig.htmlHelper')->getHostName())
                ->setViewVars([
                    'password' => $password,
                    'data' => $user
                ]);

                $newSkills = $this->request->getSession()->read('newSkillsRegistration');
                if (!empty($newSkills)) {
                    // save new skills
                    $addedSkillIds = $this->Skill->addSkills($newSkills, false, $result->uid);
                    // save id associations to user
                    $this->request = $this->request->withData('Users.skills._ids', array_merge($this->request->getData('Users.skills._ids'), $addedSkillIds));
                    $userEntity = $this->User->patchEntity($userEntity, $this->request->getData());
                    $this->User->save($userEntity);
                    $this->request->getSession()->delete('newSkillsRegistration');
                }

                $email->setTo($user['Users']['email']);

                if ($email->send()) {
                    $this->AppFlash->setFlashMessage('Deine Registrierung war erfolgreich. Bitte überprüfe dein E-Mail-Konto um deine E-Mail-Adresse zu bestätigen.');
                }

                $this->redirect(Configure::read('AppConfig.htmlHelper')->urlLogin());
            } else {
                $this->AppFlash->setFlashError('Es sind Fehler aufgetreten.');
                $this->set('user', $user);
                $this->render('register');
            }
        } else {
            $this->request->getSession()->delete('newSkillsRegistration');
        }

        $this->set('user', $user);

    }

    public function logout()
    {
        $this->AppFlash->setFlashMessage('Du hast dich erfolgreich ausgeloggt.');
        $this->Authentication->logout();
        $this->redirect('/');
    }

}

?>
