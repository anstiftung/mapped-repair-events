<?php
namespace App\Model\Table;

use App\Controller\Component\StringComponent;
use Cake\Auth\DefaultPasswordHasher;
use Cake\Core\Configure;
use Cake\Validation\Validator;
use Cake\ORM\TableRegistry;

class UsersTable extends AppTable
{

    public $name_de = 'User';

    public $allowedBasicHtmlFields = [
        'street'
    ];

    public function initialize(array $config)
    {
        parent::initialize($config);
        
        $this->belongsTo('Countries', [
            'foreignKey' => 'country_code'
        ]);
        $this->belongsToMany('Groups', [
            'joinTable' => 'users_groups',
            'foreignKey' => 'user_uid',
            'targetForeignKey' => 'group_id'
        ]);
        $this->belongsToMany('Categories', [
            'joinTable' => 'users_categories',
            'foreignKey' => 'user_uid',
            'targetForeignKey' => 'category_id'
        ]);
        $this->belongsToMany('Skills', [
            'joinTable' => 'users_skills',
            'foreignKey' => 'user_uid',
            'targetForeignKey' => 'skill_id',
            'sort' => [
                'Skills.name' => 'ASC'
            ]
        ]);
        $this->belongsToMany('Workshops', [
//             'through' => 'UsersWorkshops', // strangely this works in workshop table but not here
            'joinTable' => 'users_workshops',
            'foreignKey' => 'user_uid',
            'targetForeignKey' => 'workshop_uid'
        ]);
        $this->hasMany('OwnerWorkshops', [
            'className' => 'Workshops',
            'foreignKey' => 'owner'
        ]);
    }
    
    public function getDefaultPrivateFields()
    {
        return 'lastname,email,street,zip,phone,additional_contact';
    }
    
    private function addGroupsValidation($validator, $groups, $multiple)
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
    
    private function addLastOrgaValidation($validator)
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
                    $user = $this->find('all', [
                        'conditions' => [
                            'Users.uid' => $context['data']['uid']
                        ],
                        'contain' => [
                            'Groups'
                        ]
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
    
    public function getWorkshopsWhereUserIsLastOrgaUser($workshops)
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
    
    private function getLastOrgaValidationErrorMessage($workshops)
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
        $result = 'Du kannst die Gruppe OrganisatorIn nicht verlassen, da du bei folgenden Initiativen der/die letzte OrganisatorIn bist: ';
        $result .= join(',', $workshopLinks);
        $result .= '<br /><br /><a href="mailto:'.Configure::read('AppConfig.notificationMailAddress').'?subject=Rücktrittsanfrage für '.urlencode(join(',', $workshopNames)).'&body='.$_SESSION['Auth']['User']['name'].' ('.$_SESSION['Auth']['User']['email'].') möchte die Rolle OrganisatorIn aufgeben.">Benachrichtige den Administrator über deine Rücktrittsanfrage.</a>';
        return $result;
    }

    public function validationRegistration(Validator $validator)
    {
        $validator = $this->validationDefault($validator);
        $validator = $this->addGroupsValidation($validator, Configure::read('AppConfig.htmlHelper')->getUserGroupsForRegistration(), false);
        return $validator;
    }
    
    public function validationUserEditUser(Validator $validator)
    {
        $validator = $this->validationDefault($validator);
        $validator = $this->addLastOrgaValidation($validator);
        $validator = $this->addGroupsValidation($validator, Configure::read('AppConfig.htmlHelper')->getUserGroupsForUserEdit(false), true);
        return $validator;
    }
    
    public function validationUserEditAdmin(Validator $validator)
    {
        $validator = $this->validationDefault($validator);
        $validator = $this->addLastOrgaValidation($validator);
        $validator = $this->addGroupsValidation($validator, Configure::read('AppConfig.htmlHelper')->getUserGroupsForUserEdit(true), true);
        return $validator;
    }
    
    public function validationDefault(Validator $validator)
    {
        $validator->requirePresence('privacy_policy_accepted', true, 'Bitte akzeptiere die Datenschutzbestimmungen.');
        $validator->equals('privacy_policy_accepted', 1, 'Bitte akzeptiere die Datenschutzbestimmungen.');
        
        $validator->notEmptyString('nick', 'Bitte trage deinen Nickname ein.');
        $validator->requirePresence('nick', true, 'Bitte trage deinen Nickname ein.');
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
        
        $validator->allowEmptyString('city');
        $validator->minLength('city', 2, 'Mindestens 2 Zeichen bitte (Ort).');
        
        $validator->notEmptyString('email', 'Bitte trage deine E-Mail-Adresse ein.');
        $validator->requirePresence('email', true, 'Bitte trage deine E-Mail-Adresse ein.');
        $validator->email('email', false, 'Die E-Mail-Adresse ist ungültig.');
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
        
        return $validator;
        
    }
    
    public function validationRequestPassword(Validator $validator)
    {
        
        $validator->notEmptyString('email', 'Bitte trage deine E-Mail-Adresse ein.');
        $validator->email('email', false, 'Die E-Mail-Adresse ist ungültig.');
        $validator->add('email', 'userWithEmailExists', [
            'rule' => function ($value, $context) {
                $user = $this->find('all', [
                    'conditions' => [
                        'Users.email' => $value
                    ]
                ])
                    ->first();
                return ! empty($user);
            },
            'message' => 'Wir haben diese E-Mail-Adresse nicht gefunden.'
        ]);
        
        return $validator;
    }

    public function validationChangePassword(Validator $validator)
    {
        $validator = $this->validationDefault($validator);
        
        $validator->add('password', 'oldPasswordCheck', [
            'rule' => function ($value, $context) {
                $loggedUser = $this->find('all', [
                   'conditions' => [
                       'Users.uid' => $_SESSION['Auth']['User']['uid']
                   ] 
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
    
    public function newPasswordEqualsValidator($value, $context)
    {
        return $context['data']['password_new_1'] == $context['data']['password_new_2'];
    }

    public function newPasswordDiffersToOldValidator($value, $context)
    {
        return $context['data']['password_new_1'] != $context['data']['password'];
    }

    public function findAuth(\Cake\ORM\Query $query, array $options)
    {
        $query->where([
            'Users.status' => APP_ON
        ]);
        $query->contain([
            'Categories',
            'Groups'
        ]);
        return $query;
    }

    public function getForDropdown()
    {
        $users = $this->find('all', [
            'order' => [
                'firstname' => 'ASC',
                'lastname' => 'ASC'
            ]
        ]);
        
        $preparedUsers = [];
        foreach($users as $user) {
            $user->revertPrivatizeData();
            $preparedUsers[$user->uid] = $user->name . ' (' . $user->nick . ')';
        }
        
        return $preparedUsers;
    }

    public function setNewPassword($userUid)
    {
        $newPassword = StringComponent::createPassword();
        $user = $this->get($userUid, [
            'conditions' => [
                'Users.status >= ' . APP_DELETED
            ]
        ]);
        $user->revertPrivatizeData();
        $data = ['password' => $newPassword];
        $entity = $this->patchEntity($user, $data);
        $this->save($entity);
        return $newPassword;
    }

    /**
     * check if the given string is the password of the logged in user
     */
    public function isUserPassword($uid, $hashedPassword)
    {
        $user = $this->find('all', [
            'conditions' => [
                'Users.uid' => $uid
            ],
            'fields' => [
                'Users.password'
            ]
        ])->first();
        
        if ($hashedPassword == $user->password) {
            return true;
        } else {
            return false;
        }
    }

}

?>