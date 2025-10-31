<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\Query;
use Gregwar\Captcha\CaptchaBuilder;
use App\Model\Entity\User;
use App\Mailer\AppMailer;
use App\Model\Entity\Page;
use Cake\Http\Response;

class UsersController extends AppController
{

    public function beforeFilter(EventInterface $event): void
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

    public function all(): ?Response
    {

        // pass[0] can contain "44-tag-name" or "category-name"
        $filteredCategory = null;
        if (isset($this->getRequest()->getParam('pass')[0])) {
            /** @var \App\Model\Table\CategoriesTable */
            $categoriesTable = $this->getTableLocator()->get('Categories');
            $categories = $categoriesTable->getMainCategoriesForFrontend();
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
            /** @var \App\Model\Table\SkillsTable */
            $skillsTable = $this->getTableLocator()->get('Skills');
            $skill = $skillsTable->find('all', conditions: [
                'Skills.id' => $skillId,
                'Skills.status' => APP_ON,
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

        /** @var \App\Model\Table\UsersTable */
        $usersTable = $this->getTableLocator()->get('Users');
        $users = $usersTable->find('all',
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
                'Users.created',
                'Users.nick',
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
                return $this->redirect($correctSlug);
            }
        }

        /** @var \App\Model\Table\SkillsTable */
        $skillsTable = $this->getTableLocator()->get('Skills');
        $skillsForDropdown = $skillsTable->getForDropdownIncludingCategories();
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
        return null;
    }

    public function publicProfile(): void
    {

        $userUid = $this->request->getParam('id');

        /** @var \App\Model\Table\UsersTable */
        $usersTable = $this->getTableLocator()->get('Users');
        $workshopsAssociation = $usersTable->getAssociation('Workshops');
        $workshopsAssociation->setConditions([
            'UsersWorkshops.approved <> \'1970-01-01 00:00:00\'',
            'Workshops.status' => APP_ON,
        ]);
        $user = $usersTable->find('all',
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

        if ($this->loggedUser !== null) {
            $hasModifyPermissions = $this->loggedUser->isAdmin() || $this->loggedUser->uid == $user->uid;
            $this->set('hasModifyPermissions', $hasModifyPermissions);
            $isMyProfile = $this->loggedUser->uid == $user->uid;
            $this->set('isMyProfile', $isMyProfile);
        }

    }

    public function welcome(): void
    {
        /** @var \App\Model\Table\PagesTable */
        $pagesTable = $this->getTableLocator()->get('Pages');
        $homepageIntrotext = $pagesTable->getPageByName('homepage.introtext');
        $this->set('homepageIntrotext', $homepageIntrotext);
        $metaTags = [
            'title' => 'Herzlich Willkommen'
        ];
        $this->set('metaTags', $metaTags);
    }

    public function neuesPasswortAnfordern(): ?Response
    {
        $metaTags = [
            'title' => 'Neues Passwort anfordern'
        ];
        $this->set('metaTags', $metaTags);

        /** @var \App\Model\Table\UsersTable */
        $usersTable = $this->getTableLocator()->get('Users');
        $user = $usersTable->newEntity([], [
            'validate' => false,
        ]);

        if (! empty($this->request->getData())) {

            $user = $usersTable->newEntity($this->request->getData(), [
                'validate' => 'RequestPassword'
            ]);

            if (!($user->hasErrors())) {

                $user = $usersTable->find('all', conditions: [
                    'Users.email' => $this->request->getData('Users.email')
                ]) ->first();
                $user->revertPrivatizeData();

                $newPassword = $usersTable->setNewPassword($user->uid);

                // send email
                $email = new AppMailer();
                $email->viewBuilder()->setTemplate('new_password_request');
                $email->setSubject('Neues Passwort für '. Configure::read('AppConfig.htmlHelper')->getHostName())
                ->setViewVars([
                    'password' => $newPassword,
                    'user' => $user,
                ]);

                if (Configure::read('debug')) {
                    $email->setTo(Configure::read('AppConfig.debugMailAddress'));
                } else {
                    $email->setTo($this->request->getData('Users.email'));
                }
                $email->addToQueue();
                $this->AppFlash->setFlashMessage('Dir wurde ein neues Passwort zugeschickt.');

                $this->set('password', $newPassword);
                $this->set('user', $user);

                return $this->redirect(Configure::read('AppConfig.htmlHelper')->urlLogin());
            } else {
                $this->AppFlash->setFlashError('Es sind Fehler aufgetreten.');
            }
        }

        $this->set('user', $user);
        return null;
    }

    public function add(): Response
    {

        /** @var \App\Model\Table\UsersTable */
        $usersTable = $this->getTableLocator()->get('Users');
        $user = $usersTable->newEntity(
            [
                'private' => $usersTable->getDefaultPrivateFields()
            ],
            ['validate' => false]
        );

        $this->_profil($user, false, false);

        $metaTags = [
            'title' => 'Neuen User anlegen'
        ];
        $this->set('metaTags', $metaTags);

        return $this->render('profil');

    }

    private function _profil(User $user, bool $isMyProfile, bool $isEditMode): ?Response
    {
        /** @var \App\Model\Table\CategoriesTable */
        $categoriesTable = $this->getTableLocator()->get('Categories');
        $this->set('categories', $categoriesTable->getForDropdown([APP_ON]));

        /** @var \App\Model\Table\CountriesTable */
        $countriesTable = $this->getTableLocator()->get('Countries');
        $this->set('countries', $countriesTable->getForDropdown());

        $this->set('groups', Configure::read('AppConfig.htmlHelper')->getUserGroupsForUserEdit($this->isAdmin()));

        $this->setReferer();
        if (empty($this->request->getData())) {
            $user->private_as_array = explode(',', $user->private);
            $this->request = $this->request->withParsedBody($user);
            $this->request->getSession()->delete('newSkillsProfile');
        } else {

            /** @var \App\Model\Table\SkillsTable */
            $skillsTable = $this->getTableLocator()->get('Skills');
            $associatedSkills = $this->request->getData('Users.skills._ids');
            $newSkills = $skillsTable->getNewSkillsFromRequest($associatedSkills);
            $existingSkills = $skillsTable->getExistingSkillsFromRequest($associatedSkills);
            $this->request->getSession()->write('newSkillsProfile', $newSkills);
            $this->request = $this->request->withData('Users.skills._ids', $existingSkills);

            $addressString = trim($this->request->getData('Users.zip') . ', ' . $this->request->getData('Users.city') . ', ' . $this->request->getData('Users.country_code'));
            $geoData = $this->geoService->getGeoDataByAddress($addressString);
            $this->request = $this->request->withData('Users.lat', $geoData['lat']);
            $this->request = $this->request->withData('Users.lng', $geoData['lng']);
            $this->request = $this->request->withData('Users.province_id', $geoData['provinceId']);

            if (!empty($this->request->getData('Users.private_as_array'))) {
                $private = implode(',', $this->request->getData('Users.private_as_array'));
            } else {
                $private = '';
            }
            $this->request = $this->request->withData('Users.private', $private);

            /** @var \App\Model\Table\UsersTable */
            $usersTable = $this->getTableLocator()->get('Users');

            $user = $usersTable->patchEntity($user, $this->request->getData(), ['validate' => 'UserEdit' . ($this->isAdmin() ? 'Admin' : 'User')]);
            if (!$user->hasErrors()) {
                $usersTable->save($user);

                // update own profile
                if ($isMyProfile) {
                    $this->Authentication->setIdentity($user);
                }

                $newSkills = $this->request->getSession()->read('newSkillsProfile');
                if (!empty($newSkills)) {
                    // save new skills
                    $addedSkillIds = $skillsTable->addSkills($newSkills, $this->loggedUser->isAdmin(), $this->loggedUser->uid);
                    // save id associations to user
                    $this->request = $this->request->withData('Users.skills._ids', array_merge($this->request->getData('Users.skills._ids'), $addedSkillIds));
                    $user = $usersTable->patchEntity($user, $this->request->getData());
                    $usersTable->save($user);
                    $this->request->getSession()->delete('newSkillsProfile');
                }

                if ($isEditMode) {
                    $message = 'Das Profil wurde erfolgreich gespeichert.';
                } else {
                    $message = 'Der User wurde erfolgreich erstellt.';
                }
                $this->AppFlash->setFlashMessage($message);
                return $this->redirect($this->request->getData('referer'));
            } else {
                $this->AppFlash->setFlashError(__('An error occurred while saving the form. Please check your form.'));
            }
        }
        $this->set('user', $user);

        /** @var \App\Model\Table\PagesTable */
        $pagesTable = $this->getTableLocator()->get('Pages');
        $orgaInfotextEntity = $pagesTable->getPageByName('Was.ist.ein.Organisator');
        $orgaInfotext = '';
        if ($orgaInfotextEntity instanceof Page) {
            $orgaInfotext = $orgaInfotextEntity->text;
        }
        $this->set('orgaInfotext', $orgaInfotext);

        $repairhelperInfotextEntity = $pagesTable->getPageByName('Was.ist.ein.Reparateur');
        $repairhelperInfotext = '';
        if ($repairhelperInfotextEntity instanceof Page) {
            $repairhelperInfotext = $repairhelperInfotextEntity->text;
        }
        $this->set('repairhelperInfotext', $repairhelperInfotext);

        $this->set('isMyProfile', $isMyProfile);
        $this->set('isEditMode', $isEditMode);
        return null;
    }

    public function profil(?int $userUid=null): void
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
            $userUid = $this->loggedUser->uid;
            $metaTags = [
                'title' => 'Mein Profil'
            ];
            $isMyProfile = true;
        }

        /** @var \App\Model\Table\UsersTable */
        $usersTable = $this->getTableLocator()->get('Users');
        $user = $usersTable->find('all',
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
        /** @var \App\Model\Table\SkillsTable */
        $skillsTable = $this->getTableLocator()->get('Skills');
        $skillsForDropdown = $skillsTable->getForDropdown(true);
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

    public function passwortAendern(): ?Response
    {

        $metaTags = [
            'title' => 'Passwort ändern'
        ];
        $this->set('metaTags', $metaTags);

        /** @var \App\Model\Table\UsersTable */
        $usersTable = $this->getTableLocator()->get('Users');
        $user = $usersTable->newEntity([]);
        $this->set('user', $user);

        if (empty($this->request->getData())) {
            return null;
        }

        $user = $usersTable->newEntity([]);
        $this->set('user', $user);

        $user = $usersTable->newEntity($this->request->getData(), [
            'validate' => 'ChangePassword'
        ]);

        if (!($user->hasErrors())) {
            $user = $usersTable->get($this->isLoggedIn() ? $this->loggedUser->uid : 0);
            $user->revertPrivatizeData();
            $user2save = [
                'password' => $this->request->getData('Users.password_new_1')
            ];
            $entity = $usersTable->patchEntity($user, $user2save, ['validate' => false]);
            $usersTable->save($entity);
            $this->AppFlash->setFlashMessage('Dein Passwort wurde erfolgreich geändert.');
            return $this->redirect('/');
        }
        $this->set('user', $user);
        return null;
    }

    public function login(): ?Response
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
                return $this->redirect($target);
            } else {
                $this->AppFlash->setFlashError('Der Login hat nicht funktioniert. Benutzername oder Passwort falsch? Konto aktiviert?');
            }
        }
        return null;
    }

    public function delete(): ?Response
    {
        $metaTags = [
            'title' => 'Profil löschen'
        ];
        $this->set('metaTags', $metaTags);

        $this->setReferer();

        if (!empty($this->request->getData())) {
            $email = new AppMailer();
            $email->viewBuilder()->setTemplate('user_delete_request');
            $email->setTo(Configure::read('AppConfig.notificationMailAddress'))
            ->setSubject('User "'.$this->loggedUser->nick.'" möchte gelöscht werden')
            ->setViewVars([
                'loggedUser' => $this->loggedUser,
                'deleteMessage' => $this->request->getData('deleteMessage')
            ]);
            $email->addToQueue();
            $this->AppFlash->setFlashMessage('Deine Lösch-Anfrage wurde erfolgreich übermittelt. Wir werden dein Profil in den nächsten Tagen löschen.');
            return $this->redirect('/');
        }
        return null;
    }

    public function activate(): ?Response
    {

        if (! isset($this->request->getParam('pass')['0'])) {
            $this->AppFlash->setFlashError(__('Invalid parameters.'));
            return null;
        }

        $conditions = [
            'Users.status >= ' . APP_OFF
        ];

        /** @var \App\Model\Table\UsersTable */
        $usersTable = $this->getTableLocator()->get('Users');
        $user = $usersTable->find('all', conditions: array_merge($conditions, [
            'Users.confirm' => $this->request->getParam('pass')['0']
        ]))->first();

        if (empty($user)) {
            $this->AppFlash->setFlashError(__('Invalid activation code.'));
            return null;
        }

        $user = $usersTable->get($user->uid,
        conditions: $conditions,
        contain: [
            'Categories',
            'Groups'
        ]);
        $user->revertPrivatizeData();
        $user2save = [
            'confirm' => User::STATUS_OK,
            'status' => APP_ON,
        ];
        $entity = $usersTable->patchEntity($user, $user2save, ['validate' => false]);
        $usersTable->save($entity);

        $this->Authentication->setIdentity($user);
        $this->AppFlash->setFlashMessage('Dein Account ist nun aktiviert, du bist eingeloggt und kannst deine Profildaten ergänzen bzw. dein Passwort ändern.<br />Dein akuelles Passwort wurde dir soeben zugesendet.');
        
        $newPassword = $usersTable->setNewPassword($user->uid);
        
        $email = new AppMailer();
        $email->viewBuilder()->setTemplate('activation_successful');
        $email->setSubject('Deine Aktivierung bei '. Configure::read('AppConfig.htmlHelper')->getHostName() . ' war erfolgreich')
        ->setViewVars([
            'password' => $newPassword,
            'user' => $user,
        ]);
        if (Configure::read('debug')) {
            $email->setTo(Configure::read('AppConfig.debugMailAddress'));
        } else {
            $email->setTo($user->email);
        }
        $email->addToQueue();

        return$this->redirect(Configure::read('AppConfig.htmlHelper')->urlUserHome());

    }

    public function registerRepairhelper(): ?Response
    {
        $this->register(GROUPS_REPAIRHELPER);
        // assures rendering of success message on redirected page and NOT before and then not showing it
        if (empty($this->request->getData())) {
            return $this->render('register');
        }
        return null;
    }

    public function registerOrga(): ?Response
    {
        $this->register(GROUPS_ORGA);
        // assures rendering of success message on redirected page and NOT before and then not showing it
        if (empty($this->request->getData())) {
            return $this->render('register');
        }
        return null;
    }

    private function isCalledByTestSuite(): bool
    {
        return !empty($_SERVER['argv']) && !empty($_SERVER['argv'][0]) && preg_match('`vendor/bin/phpunit`', (string) $_SERVER['argv'][0]);
    }

    public function register(int $userGroup=GROUPS_REPAIRHELPER): ?Response
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

        /** @var \App\Model\Table\CountriesTable */
        $countriesTable = $this->getTableLocator()->get('Countries');
        $this->set('countries', $countriesTable->getForDropdown());

        /** @var \App\Model\Table\CategoriesTable */
        $categoriesTable = $this->getTableLocator()->get('Categories');
        $this->set('categories', $categoriesTable->getForDropdown([APP_ON]));

        /** @var \App\Model\Table\SkillsTable */
        $skillsTable = $this->getTableLocator()->get('Skills');
        $this->set('skillsForDropdown', $skillsTable->getForDropdown(false));

        $this->set('groups', Configure::read('AppConfig.htmlHelper')->getUserGroupsForRegistration());

        $metaTags = [
            'title' => 'Registrierung - Wähle deine Rolle: Organisator*in oder ' . Configure::read('AppConfig.repairHelperName')
        ];
        $this->set('metaTags', $metaTags);

        /** @var \App\Model\Table\UsersTable */
        $usersTable = $this->getTableLocator()->get('Users');
        $user = $usersTable->newEmptyEntity();

        if (! empty($this->request->getData())) {

            $associatedSkills = $this->request->getData('Users.skills._ids');
            $newSkills = $skillsTable->getNewSkillsFromRequest($associatedSkills);
            $existingSkills = $skillsTable->getExistingSkillsFromRequest($associatedSkills);
            $this->request->getSession()->write('newSkillsRegistration', $newSkills);
            $this->request = $this->request->withData('Users.skills._ids', $existingSkills);

            $this->request = $this->request->withData('groups', ['_ids' => [$userGroup]]);
            $user = $usersTable->patchEntity($user, $this->request->getData(), ['validate' => 'Registration']);

            if (!$this->isCalledByTestSuite()) {
                /* @phpstan-ignore-next-line */
                $captchaBuilder->setPhrase($this->request->getSession()->read('captchaPhrase'));
                /* @phpstan-ignore-next-line */
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
                $user['Users']['private'] = $usersTable->getDefaultPrivateFields();
                $userEntity = $usersTable->newEntity($user, ['validate' => 'Registration']);
                $userEntity = $this->stripTagsFromFields($userEntity, 'User');
                $result = $usersTable->save($userEntity);

                $email = new AppMailer();
                $email->viewBuilder()->setTemplate('registration_successful');
                $email->setSubject('Deine Registrierung bei '. Configure::read('AppConfig.htmlHelper')->getHostName())
                ->setViewVars([
                    'data' => $user,
                ]);
                $email->setTo($user['Users']['email']);
                $email->addToQueue();

                $newSkills = $this->request->getSession()->read('newSkillsRegistration');
                if (!empty($newSkills)) {
                    // save new skills
                    $addedSkillIds = $skillsTable->addSkills($newSkills, false, $result->uid);
                    // save id associations to user
                    $this->request = $this->request->withData('Users.skills._ids', array_merge($this->request->getData('Users.skills._ids'), $addedSkillIds));
                    $userEntity = $usersTable->patchEntity($userEntity, $this->request->getData());
                    $usersTable->save($userEntity);
                    $this->request->getSession()->delete('newSkillsRegistration');
                }

                $this->AppFlash->setFlashMessage('Deine Registrierung war erfolgreich. Bitte überprüfe dein E-Mail-Konto um deine E-Mail-Adresse zu bestätigen.');

                return $this->redirect(Configure::read('AppConfig.htmlHelper')->urlLogin());
            } else {
                $this->AppFlash->setFlashError('Es sind Fehler aufgetreten.');
                $this->set('user', $user);
                return $this->render('register');
            }
        } else {
            $this->request->getSession()->delete('newSkillsRegistration');
        }

        $this->set('user', $user);
        return null;

    }

    public function logout(): Response
    {
        $this->AppFlash->setFlashMessage('Du hast dich erfolgreich ausgeloggt.');
        $this->Authentication->logout();
        return $this->redirect('/');
    }

}

?>
