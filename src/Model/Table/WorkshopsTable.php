<?php
namespace App\Model\Table;

use Cake\Validation\Validator;
use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;

class WorkshopsTable extends AppTable
{

    public const STATISTICS_DISABLED = 0;
    public const STATISTICS_SHOW_ALL = 1;
    public const STATISTICS_SHOW_ONLY_CHART = 2;

    public $name_de = '';

    public $allowedBasicHtmlFields = [
        'additional_contact',
        'opening_hours',
        'rechtl_vertret',
        'street'
    ];

    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->name_de = Configure::read('AppConfig.initiativeNameSingular');

        $this->belongsTo('Countries', [
            'foreignKey' => 'country_code'
        ]);
        $this->hasMany('Events', [
            'foreignKey' => 'workshop_uid',
            'conditions' => [
                'Events.datumstart != \'0000-00-00\'',
                'Events.status' => APP_ON
            ],
            'sort' => [
                'Events.datumstart' => 'ASC'
            ]
        ]);
        // necessary to retrieve additional data of relation table (eg. Workshops.apply)
        $this->hasMany('UsersWorkshops', [
            'foreignKey' => 'workshop_uid',
            'targetForeignKey' => 'user_uid'
        ]);
        $this->belongsToMany('Users', [
            'through' => 'UsersWorkshops',
            'foreignKey' => 'workshop_uid',
            'targetForeignKey' => 'user_uid',
            'sort' => [
                'Users.lastname' => 'ASC'
            ]
        ]);
        $this->belongsToMany('Categories', [
            'joinTable' => 'workshops_categories',
            'foreignKey' => 'workshop_uid',
            'targetForeignKey' => 'category_id'
        ]);
    }

    public function validationDefault(Validator $validator): \Cake\Validation\Validator
    {
        $validator = $this->validationAdmin($validator);
        $invalidCoordinateMessage = 'Die Adresse wurde nicht gefunden. Bitte ändere sie oder lege die Koordinaten selbst fest.';
        $validator->numeric('lat', $invalidCoordinateMessage);
        $validator->numeric('lng', $invalidCoordinateMessage);
        $validator->equals(0, $invalidCoordinateMessage);
        $validator->equals(0, $invalidCoordinateMessage);
        $validator->notEmptyString('city', 'Bitte trage die Stadt ein.');
        $validator->minLength('city', 2, 'Bitte trage die Stadt ein.');
        $validator->notEmptyString('country_code', 'Bitte wähle dein Land aus.');
        $validator->notEmptyString('zip', 'Bitte trage die PLZ ein.');
        $validator->notEmptyString('street', 'Bitte trage die Straße ein.');
        $validator->minLength('street', 2, 'Bitte trage die Straße ein.');
        $validator->decimal('lat', null, 'Bitte gib eine Zahl ein.');
        $validator->decimal('lng', null, 'Bitte gib eine Zahl ein.');
        $validator->add('zip', 'validFormat', [
            'rule' => ['custom', ZIP_REGEX],
            'message' => 'Die PLZ ist nicht gültig.'
        ]);
        return $validator;
    }

    public function validationAdmin(Validator $validator)
    {
        $validator = $this->getNumberRangeValidator($validator, 'lat', -90, 90);
        $validator = $this->getNumberRangeValidator($validator, 'lng', -180, 180);
        $validator = parent::addUrlValidation($validator);
        $validator->notEmptyString('name', 'Bitte trage den Namen der '.Configure::read('AppConfig.initiativeNameSingular') .' ein.');
        $validator->minLength('name', 2, 'Bitte gib einen gültigen Namen an.');
        $validator = $this->addBlockedWorkshopSlugsValidationRule($validator);
        $validator->url('website', 'Bitte trage eine gültige Url ein.');
        $validator->allowEmptyString('website');
        $validator->email('email', true, 'Bitte trage eine gültige E-Mail-Adresse ein.');
        $validator->notEmptyString('email', 'Bitte trage deine E-Mail-Adresse ein.');
        $validator->inList(
            'show_statistics',
            [
                self::STATISTICS_DISABLED,
                self::STATISTICS_SHOW_ALL,
                self::STATISTICS_SHOW_ONLY_CHART,
            ],
            'Dieser Wert ist nicht gültig.',
        );
        return $validator;
    }

    /**
     *
     * @param int $userUid
     * @return array
     */
    public function getWorkshopsForAssociatedUser($userUid, $workshopStatus)
    {
        $workshops = $this->getWorkshopsWithUsers($workshopStatus);
        $workshops->matching('Users', function ($q) use ($userUid) {
            return $q->where([
                'UsersWorkshops.user_uid' => $userUid,
                'UsersWorkshops.approved <> \'1970-01-01 00:00:00\''
            ]);
        });
        // revertPrivatizeData needs to be called again (although already applied in getWorkshopsWithUsers)
        foreach($workshops as $workshop) {
            foreach($workshop->users as $user) {
                $user->revertPrivatizeData();
            }
        }
        return $workshops;
    }

    public function getWorkshopsForAdmin($workshopStatus)
    {
        return $this->getWorkshopsWithUsers($workshopStatus);
    }

    public function transformForDropdown($workshops)
    {
        $result = [];
        foreach($workshops as $workshop) {
            $result[$workshop->uid] = $workshop->name;
        }
        return $result;
    }

    public function getWorkshopsWithUsers($workshopStatus)
    {
        $workshops = $this->find('all', [
            'conditions' => [
                'Workshops.status > ' . $workshopStatus
            ],
            'contain' => [
                'Users',
                'Users.Groups'
            ],
            'order' => [
                'Workshops.name' => 'ASC'
            ]
        ]);
        foreach($workshops as $workshop) {
            foreach($workshop->users as $user) {
                $user->revertPrivatizeData();
            }
        }
        return $workshops;
    }

    private function addBlockedWorkshopSlugsValidationRule($validator)
    {
        $validator->add('url', 'addBlockedWorkshopSlugsValidationRule', [
            'rule' => function($value, $context) {
                $bws = FactoryLocator::get('Table')->get('BlockedWorkshopSlugs');
                $recordCount = $bws->find('all', [
                    'conditions' => [
                        'status' => APP_ON,
                        'url' => $value
                    ],
                ])->count();
                if ($recordCount == 0) {
                    return true;
                }
                return false;
            },
            'message' => 'Dieser Slug darf nicht verwendet werden.'
        ]);
        return $validator;

    }

    public function getTeam($workshop)
    {
        return $workshop->users;
    }

    /**
     * returns owner and approved users_workshops users with group $orgaTeamGroups
     * @param $workshop
     * @return array
     */
    public function getOrgaTeam($workshop)
    {
        $orgaTeam = $this->getTeam($workshop);
        if (!empty($orgaTeam)) {
            $i = 0;
            foreach($orgaTeam as $user) {
                if (isset($user->groups)) {
                    $removeUserFromTeam = true;
                    foreach($user->groups as $group) {
                        if (in_array($group->id, [GROUPS_ORGA])) {
                            $removeUserFromTeam = false;
                        }
                    }
                    if ($removeUserFromTeam) {
                        unset($orgaTeam[$i]);
                    }
                }
                $i++;
            }
        }
        return $orgaTeam;
    }

    public function getWorkshopForIsUserInOrgaTeamCheck($workshopUid)
    {
        $usersAssociation = $this->getAssociation('Users');
        $usersAssociation->setConditions([
            'UsersWorkshops.approved <> \'1970-01-01 00:00:00\''
        ]);
        $workshop = $this->find('all', [
            'conditions' => [
                'Workshops.uid' => $workshopUid,
                'Workshops.status >= ' . APP_OFF
            ],
            'contain' => [
                'Users',
                'Users.Groups'
            ]
        ])->first();
        return $workshop;
    }

    public function isUserInOrgaTeam($user, $workshop)
    {
        if (empty($user)) {
            return false;
        }
        $orgaTeam = $this->getOrgaTeam($workshop);
        $userFound = false;
        foreach($orgaTeam as $orgaTeamUser) {
            if ($user['uid'] == $orgaTeamUser->uid) {
                $userFound = true;
                break;
            }
        }
        return $userFound;
    }

    public function getKeywordSearchConditions($keyword, $negate) {
        return function ($exp, $query) use ($keyword, $negate) {
            $result = $exp->or([
                'Workshops.city LIKE' => $keyword . '%',
                'Workshops.zip LIKE' => $keyword . '%',
                'Workshops.name LIKE' => $keyword . '%',
            ]);
            if ($negate) {
                $result = $exp->not($result);
            }
            return $result;
        };
    }

    public function getLatestWorkshops() {
        $workshops = $this->find('all', [
            'fields' => [
                'Workshops.uid',
                'Workshops.name',
                'Workshops.text',
                'Workshops.url',
                'Workshops.image',
                'Workshops.city',
                'Workshops.zip',
                'Workshops.street'
            ]
            ,'limit' => 3
            ,'order' => 'RAND()'
            ,'conditions' => [
                'Workshops.status' => APP_ON,
                'Workshops.image != ' => ''
            ]
        ]);
        return $workshops;
    }

    /**
     *
     * @param int $workshopUid
     * @return boolean
     */
    public function isLoggedUserApproved($workshopUid, $userUid)
    {
        $usersAssociation = $this->getAssociation('UsersWorkshops');
        $usersAssociation->setConditions([
            'user_uid' => $userUid,
            'approved <> ' => '0000-00-00 00:00'
        ]);
        $workshop = $this->find('all', [
            'conditions' => [
                'Workshops.uid' => $workshopUid,
                'Workshops.status > ' => APP_DELETED
            ],
            'contain' => [
                'UsersWorkshops'
            ]
        ])->first();

        if (!empty($workshop->users_workshops)) {
            return true;
        } else {
            return false;
        }
    }

    public function getForDropdown()
    {
        $workshops = $this->find('all', [
            'conditions' => [
                'Workshops.name <> ""',
                'Workshops.status' => APP_ON
            ],
            'order' => [
                'Workshops.name' => 'ASC'
            ]
        ]);
        $preparedWorkshops = [];
        foreach($workshops as $workshop) {
            $preparedWorkshops[$workshop->uid] = $workshop->name;
        }
        return $preparedWorkshops;
    }
}
?>