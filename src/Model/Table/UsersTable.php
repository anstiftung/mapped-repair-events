<?php
declare(strict_types=1);
namespace App\Model\Table;

use ArrayObject;
use Cake\Utility\Hash;
use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\ORM\RulesChecker;
use Cake\Event\EventInterface;
use Cake\Validation\Validator;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\Datasource\EntityInterface;
use App\Model\Rule\UserLinkToWorkshopRule;
use App\Controller\Component\StringComponent;
use Cake\ORM\TableRegistry;
use Cake\ORM\Query\SelectQuery;

class UsersTable extends AppTable
{

    public string  $name_de = 'User';

    public array $allowedBasicHtmlFields = [
        'street'
    ];

    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void
    {
        if (isset($data['website'])) {
            $data['website'] = StringComponent::addProtocolToUrl($data['website']);
        }
    }

    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('Countries', [
            'foreignKey' => 'country_code'
        ]);
        $this->belongsTo('Provinces', [
            'foreignKey' => 'province_id',
        ]);
        $this->belongsToMany('Groups', [
            'joinTable' => 'users_groups',
            'foreignKey' => 'user_uid',
            'targetForeignKey' => 'group_id',
            'dependent' => true,
        ]);
        $this->belongsToMany('Categories', [
            'joinTable' => 'users_categories',
            'foreignKey' => 'user_uid',
            'targetForeignKey' => 'category_id',
            'dependent' => true,
        ]);
        $this->belongsToMany('Skills', [
            'joinTable' => 'users_skills',
            'foreignKey' => 'user_uid',
            'targetForeignKey' => 'skill_id',
            'sort' => [
                'Skills.name' => 'ASC'
            ],
            'dependent' => true,
        ]);
        $this->belongsToMany('Workshops', [
//             'through' => 'UsersWorkshops', // strangely this works in workshop table but not here
            'joinTable' => 'users_workshops',
            'foreignKey' => 'user_uid',
            'targetForeignKey' => 'workshop_uid',
            'dependent' => true,
        ]);
        $this->hasMany('OwnerWorkshops', [
            'className' => 'Workshops',
            'foreignKey' => 'owner'
        ]);
    }

    public function getDefaultPrivateFields(): string
    {
        return 'lastname,email,street,zip,phone,additional_contact';
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->addDelete(
            new UserLinkToWorkshopRule(),
            null,
            [
                'errorField' => 'workshops',
            ]
        );
        return $rules;
    }

    public function beforeDelete(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {

        $userUid = $entity->get('uid');

        // 1. set owner to 0 for all deleted workshops where to-be-deleted user was owner
        $workshopTable = TableRegistry::getTableLocator()->get('Workshops');
        $deletedOwnerWorkshops = $workshopTable->find('all',
            conditions: [
                'Workshops.owner' => $userUid,
                'Workshops.status' => APP_DELETED,
            ],
        )->toArray();

        if (!empty($deletedOwnerWorkshops)) {
            $workshopTable->updateAll([
                'owner' => 0,
            ], [
                'Workshops.uid IN' => Hash::extract($deletedOwnerWorkshops, '{n}.uid'),
            ]);
        }

        // 2. set owner to admin-user for all non-deleted workshops where to-be-deleted user was owner
        $nonDeletedOwnerWorkshops = $workshopTable->find('all',
            conditions: [
                'Workshops.owner' =>$userUid,
                'Workshops.status > ' . APP_DELETED,
            ],
        )->toArray();

        if (!empty($nonDeletedOwnerWorkshops)) {
            $workshopTable->updateAll([
                'owner' => Configure::read('AppConfig.adminUserUid'),
            ], [
                'Workshops.uid IN' => Hash::extract($nonDeletedOwnerWorkshops, '{n}.uid'),
            ]);
        }

        // 3. delete user profile image
        if ($entity->image != '') {

            $fileName = explode('?', $entity->image);
            $fileName = $fileName[0];

            $thumbSizes = Configure::read('AppConfig.thumbSizes');
            foreach ($thumbSizes as $thumbSize => $thumbSizeOptions) {
                $thumbMethod = 'getThumbs' . $thumbSize . 'Image';
                $thumbsFileName = Configure::read('AppConfig.htmlHelper')->$thumbMethod($fileName, 'users');
                $targetFileAbsolute = str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'] . $thumbsFileName);
                unlink($targetFileAbsolute);
            }

            $originalFileName =  Configure::read('AppConfig.htmlHelper')->getOriginalImage($fileName, 'users');
            $targetFileAbsolute = str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'] . $originalFileName);
            unlink($targetFileAbsolute);

        }
    }

    private function addGroupsValidation(Validator $validator, array $groups, bool $multiple): Validator
    {
        $validator->add('groups', 'checkForAllowedGroups', [
            'rule' => function($value, $context) use ($groups, $multiple) {
                $result = false;
                if (!is_array($value['_ids'])) {
                    return false;
                }
                if ($multiple && count($value['_ids']) > 1) {
                    $result = true;
                }
                $result = in_array($value['_ids'][0], array_keys($groups));
                return $result;
            },
            'message' => ($multiple ? 'Deine Rollen sind' : 'Deine Rolle ist') . ' nicht gültig. Bitte wähle mindestens eine Rolle aus.'
        ]);
        return $validator;
    }

    private function addLastOrgaValidation(Validator $validator): Validator
    {
        $validator->add('groups', 'checkLastOrga', [
            'rule' => function($value, $context) {

                // never check on creating a user
                if (!isset($context['data']['uid'])) {
                    return true;
                }

                // only apply rule if checkbox was removed and user was orga user
                if (is_array($context['data']['groups']['_ids']) && in_array(GROUPS_ORGA, $context['data']['groups']['_ids'])) {
                    return true;
                } else {
                    $user = $this->find('all',
                    conditions: [
                        'Users.uid' => $context['data']['uid']
                    ],
                    contain: [
                        'Groups'
                    ])->first();
                    $groupsTable = TableRegistry::getTableLocator()->get('Groups');
                    if (!$groupsTable->isOrga($user)) {
                        return true;
                    }
                }

                // actual check if user is last orga user in any of "his" workshops starts here
                $workshopTable = TableRegistry::getTableLocator()->get('Workshops');
                $workshops = $workshopTable->getWorkshopsForAssociatedUser($context['data']['uid'], APP_OFF);
                $associatedWorkshopsWhereUserIsLastOrgaUser = $this->getWorkshopsWhereUserIsLastOrgaUser($workshops);
                if (!empty($associatedWorkshopsWhereUserIsLastOrgaUser)) {
                    return $this->getLastOrgaValidationErrorMessage($associatedWorkshopsWhereUserIsLastOrgaUser);
                }
                return true;
            }
        ]);
        return $validator;
    }

    public function getWorkshopsWhereUserIsLastOrgaUser(SelectQuery $workshops): array
    {
        $lastOrgaWorkshops = [];
        foreach($workshops as $workshop) {
            $sumOrgaUsers = 0;
            foreach($workshop->users as $user) {
                if (is_null($user->_joinData->approved)) {
                    continue;
                }
                foreach($user->groups as $group) {
                    if ($group->id == GROUPS_ORGA) {
                        $sumOrgaUsers++;
                    }
                }
            }
            if ($sumOrgaUsers == 1) {
                $lastOrgaWorkshops[] = $workshop;
            }
        }
        return $lastOrgaWorkshops;
    }

    private function getLastOrgaValidationErrorMessage(array $workshops): string
    {
        $workshopLinks = [];
        $workshopNames = [];
        foreach($workshops as $workshop) {
            $workshopLinks [] = Configure::read('AppConfig.htmlHelper')->link(
                $workshop->name,
                Configure::read('AppConfig.htmlHelper')->urlWorkshopDetail($workshop->url),
                ['target' => '_blank']
            );
            $workshopNames[] = '"'.$workshop->name.'"';
        }
        $result = 'Du kannst die Gruppe Organisator*in nicht verlassen, da du bei folgenden Initiativen der/die letzte Organisator*in bist: ';
        $result .= join(',', $workshopLinks);
        $result .= '<br /><br /><a href="mailto:'.Configure::read('AppConfig.notificationMailAddress').'?subject=Rücktrittsanfrage für '.urlencode(join(',', $workshopNames)).'&body='.$_SESSION['Auth']['User']['name'].' ('.$_SESSION['Auth']['User']['email'].') möchte die Rolle Organisator*in aufgeben.">Benachrichtige den Administrator über deine Rücktrittsanfrage.</a>';
        return $result;
    }

    public function validationRegistration(Validator $validator): Validator
    {
        $validator = $this->validationDefault($validator);
        $validator->requirePresence('privacy_policy_accepted', true, 'Bitte akzeptiere die Datenschutzbestimmungen.');
        $validator->equals('privacy_policy_accepted', 1, 'Bitte akzeptiere die Datenschutzbestimmungen.');
        $validator = $this->addGroupsValidation($validator, Configure::read('AppConfig.htmlHelper')->getUserGroupsForRegistration(), false);
        return $validator;
    }

    public function validationUserEditUser(Validator $validator): Validator
    {
        $validator = $this->validationDefault($validator);
        $validator = $this->addLastOrgaValidation($validator);
        $validator = $this->addGroupsValidation($validator, Configure::read('AppConfig.htmlHelper')->getUserGroupsForUserEdit(false), true);
        return $validator;
    }

    public function validationUserEditAdmin(Validator $validator): Validator
    {
        $validator = $this->validationDefault($validator);
        $validator = $this->addLastOrgaValidation($validator);
        $validator = $this->addGroupsValidation($validator, Configure::read('AppConfig.htmlHelper')->getUserGroupsForUserEdit(true), true);
        return $validator;
    }

    public function validationFunding(Validator $validator): Validator
    {
        $validator->notEmptyString('nick', 'Bitte trage deinen Nickname ein.');
        $validator->minLength('nick', 2, 'Mindestens 2 Zeichen bitte (Nickname).');
        $validator->add('nick', 'unique', [
            'rule' => 'validateUnique',
            'provider' => 'table',
            'message' => 'Dieser Nickname wird bereits verwendet.'
        ]);

        $validator->notEmptyString('firstname', 'Bitte trage deinen Vornamen ein.');
        $validator->requirePresence('firstname', true, 'Bitte trage deinen Vornamen ein.');
        $validator->minLength('firstname', 2, 'Mindestens 2 Zeichen bitte (Vorname).');
        
        $validator->notEmptyString('lastname', 'Bitte trage deinen Nachnamen ein.');
        $validator->requirePresence('lastname', true, 'Bitte trage deinen Nachnamen ein.');
        $validator->minLength('lastname', 2, 'Mindestens 2 Zeichen bitte (Nachname).');

        $validator->minLength('city', 2, 'Mindestens 2 Zeichen bitte (Ort).');

        $validator->url('website', 'Bitte trage eine gültige Url ein.');
        $validator->allowEmptyString('website');

        $validator->notEmptyString('email', 'Bitte trage deine E-Mail-Adresse ein.');
        $validator->requirePresence('email', true, 'Bitte trage deine E-Mail-Adresse ein.');
        $validator->email('email', true, 'Bitte trage eine gültige E-Mail-Adresse ein.');
        $validator->add('email', 'unique', [
            'rule' => 'validateUnique',
            'provider' => 'table',
            'message' => 'Diese E-Mail-Adresse wird bereits verwendet.'
        ]);

        $validator->requirePresence('zip', true, 'Bitte trage deine PLZ ein.');
        $validator->notEmptyString('zip', 'Bitte trage deine PLZ ein.');
        $validator->add('zip', 'validFormat', [
            'rule' => array('custom', ZIP_REGEX),
            'message' => 'Die PLZ ist nicht gültig.'
        ]);

        $validator->notEmptyString('phone', 'Bitte trage deine Telefonnummer ein.');
        $validator->notEmptyString('street', 'Bitte trage deine Straße + Hausnummer ein.');

        return $validator;

    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator = $this->validationFunding($validator);
        $validator->allowEmptyString('street');
        $validator->allowEmptyString('phone');
        $validator->allowEmptyString('city');
        return $validator;
    }

    public function validationRequestPassword(Validator $validator): Validator
    {

        $validator->notEmptyString('email', 'Bitte trage deine E-Mail-Adresse ein.');
        $validator->email('email', false, 'Die E-Mail-Adresse ist ungültig.');
        $validator->add('email', 'userWithEmailExists', [
            'rule' => function ($value, $context) {
                $user = $this->find('all', conditions: [
                    'Users.email' => $value
                ])
                    ->first();
                return ! empty($user);
            },
            'message' => 'Wir haben diese E-Mail-Adresse nicht gefunden.'
        ]);

        return $validator;
    }

    public function validationChangePassword(Validator $validator): Validator
    {
        $validator->add('password', 'oldPasswordCheck', [
            'rule' => function ($value, $context) {
                $loggedUserUid = Router::getRequest()?->getAttribute('identity')?->uid;
                $loggedUser = $this->find('all', conditions: [
                    'Users.uid' => $loggedUserUid,
                ])->first();
                return (new DefaultPasswordHasher)->check($value, $loggedUser->password);
            },
            'message' => 'Dein altes Passwort war falsch.'
        ]);

        $validator->add('password_new_1', 'newPasswordRegexCheck', [
            'rule' => function ($value, $context) {
                return (bool) preg_match(PASSWORD_REGEX, $value);
            },
            'message' => '10 - 32 Zeichen bitte.'
        ]);

        $validator->add('password_new_1', 'newPasswordsEqual', [
            'rule' => 'newPasswordEqualsValidator',
            'provider' => 'table',
            'message' => 'Deine neuen Passwörter sind nicht gleich.',
            'allowEmpty' => false
        ]);

        $validator->add('password_new_2', 'newPasswordsEqual', [
            'rule' => 'newPasswordEqualsValidator',
            'provider' => 'table',
            'message' => 'Deine neuen Passwörter sind nicht gleich.',
            'allowEmpty' => false
        ]);

        $validator->add('password_new_1', 'newPasswordsDiffersToOld', [
            'rule' => 'newPasswordDiffersToOldValidator',
            'provider' => 'table',
            'message' => 'Dein neues Passwort muss anders lauten als dein altes.',
            'allowEmpty' => false
        ]);

        $validator->add('password_new_2', 'newPasswordsDiffersToOld', [
            'rule' => 'newPasswordDiffersToOldValidator',
            'provider' => 'table',
            'message' => 'Dein neues Passwort muss anders lauten als dein altes.',
            'allowEmpty' => false
        ]);
        $validator->notEmptyString('password', 'Bitte gib dein altes Passwort an.');
        $validator->notEmptyString('password_new_1', 'Bitte gib dein neues Passwort an.');
        $validator->notEmptyString('password_new_2', 'Bitte gib dein neues Passwort an.');

        return $validator;
    }

    public function newPasswordEqualsValidator(string $value, array $context): bool
    {
        return $context['data']['password_new_1'] == $context['data']['password_new_2'];
    }

    public function newPasswordDiffersToOldValidator(string $value, array $context): bool
    {
        return $context['data']['password_new_1'] != $context['data']['password'];
    }

    public function findAuth(SelectQuery $query, array $options): SelectQuery
    {
        $query->where([
            'Users.status' => APP_ON,
        ]);
        $query->contain([
            'Categories',
            'Groups',
        ]);
        return $query;
    }

    public function getForDropdown(): array
    {
        $users = $this->find('all',
        conditions: [
            'Users.status > ' . APP_DELETED,
        ],
        order: [
            'firstname' => 'ASC',
            'lastname' => 'ASC'
        ]);

        $preparedUsers = [];
        foreach($users as $user) {
            $user->revertPrivatizeData();
            $preparedUsers[$user->uid] = $user->name . ' (' . $user->nick . ')';
        }

        return $preparedUsers;
    }

    public function setNewPassword(int $userUid): string
    {
        $newPassword = StringComponent::createPassword();
        $user = $this->get($userUid, conditions: [
            'Users.status >= ' . APP_DELETED
        ]);
        $user->revertPrivatizeData();
        $data = ['password' => $newPassword];
        $entity = $this->patchEntity($user, $data, ['validate' => false]);
        $this->save($entity);
        return $newPassword;
    }

    /**
     * check if the given string is the password of the logged in user
     */
    public function isUserPassword(int $uid, string $hashedPassword): bool
    {
        $user = $this->find('all',
        conditions: [
            'Users.uid' => $uid,
        ],
        fields: [
            'Users.password',
        ])->first();

        return $hashedPassword == $user->password;
    }

}

?>