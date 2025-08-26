<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\Validation\Validator;
use App\Model\Traits\SearchExceptionsTrait;
use App\Services\GeoService;
use Cake\ORM\Query\SelectQuery;
use App\Model\Entity\Event;
use Cake\Collection\CollectionInterface;
use Cake\Routing\Router;

class EventsTable extends AppTable
{

    use SearchExceptionsTrait;

    public string $name_de = 'Termin';

    /**
     * @var string[]
     */
    public array $allowedBasicHtmlFields = [
        'eventbeschreibung'
    ];

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->belongsTo('Workshops', [
            'foreignKey' => 'workshop_uid'
        ]);
        $this->belongsTo('Provinces', [
            'foreignKey' => 'province_id',
        ]);
        $this->hasMany('InfoSheets', [
            'foreignKey' => 'event_uid',
        ]);
        $this->belongsToMany('Categories', [
            'joinTable' => 'events_categories',
            'foreignKey' => 'event_uid',
            'targetForeignKey' => 'category_id'
        ]);
		$this->hasMany('EventCategories', [
			'foreignKey' => 'event_uid',
			'dependent'  => true,
		]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $geoService = new GeoService();
        $validator = $geoService->getGeoCoordinatesValidator($validator);
        $validator->notEmptyString('workshop_uid', 'Bitte wähle eine Initiative aus.');
        $validator->notEmptyDate('datumstart', 'Bitte trage ein Datum ein.');
        $validator->notEmptyTime('uhrzeitstart', 'Bitte trage eine von-Uhrzeit ein.');
        $validator->notEmptyTime('uhrzeitend', 'Bitte trage eine bis-Uhrzeit ein.');
        $invalidCoordinateMessage = 'Die Adresse wurde nicht gefunden. Bitte ändere sie oder lege die Koordinaten selbst fest.';
        $validator->numeric('lat', $invalidCoordinateMessage);
        $validator->numeric('lng', $invalidCoordinateMessage);
        $validator->notEmptyString('workshop_uid', 'Bitte wähle eine Initiative aus.');
        $validator->notEmptyString('ort', 'Bitte trage die Stadt ein.');
        $validator->minLength('ort', 2, 'Bitte trage die Stadt ein.');
        $validator->notEmptyString('strasse', 'Bitte trage die Straße ein.');
        $validator->minLength('strasse', 2, 'Bitte trage die Straße ein.');
        $validator->notEmptyString('zip', 'Bitte trage die PLZ ein.');
        $validator->decimal('lat', null, 'Bitte gib eine Zahl ein.');
        $validator->decimal('lng', null, 'Bitte gib eine Zahl ein.');
        $validator->add('zip', 'validFormat', [
            'rule' => ['custom', ZIP_REGEX],
            'message' => 'Die PLZ ist nicht gültig.'
        ]);
        $validator = $this->addDuplicateEventValidationRule($validator);
        return $validator;
    }

    private function addDuplicateEventValidationRule(Validator $validator): Validator
    {
        /*
        $validator->add('datumstart', 'duplicateEventNotYetSaved', [
            'rule' => function($value, $context): bool {

                $events = [];
                foreach(Router::getRequest()->getData() as $data) {
                    if (!is_array($data)) {
                       continue; // skip referer
                    }
                    if (!array_key_exists('datumstart', $data)) {
                        continue; // skip metadata (fields / unlocked / debug)
                    }

                    $events[] = $data;
                }

                $keys = array_map(function($event) {
                    return $event['datumstart'] . '|' . $event['uhrzeitstart'] . '|' . $event['uhrzeitend'];
                }, $events);

                $currentEntityKey = $context['data']['datumstart']->format('m.d.Y') . '|' . $context['data']['uhrzeitstart']->format('H:i') . '|' . $context['data']['uhrzeitend']->format('H:i');
                return count($keys) !== count(array_unique($keys)) && in_array($currentEntityKey, $keys);
            },
            'message' => 'Du kannst keine Veranstaltungen zur gleichen Zeit am gleichen Tag anlegen.',
        ]);
        */
        $validator->add('datumstart', 'duplicateEventAlreadySaved', [
            'rule' => function($value, $context): bool {
                // Skip validation if required fields are missing
                if (empty($context['data']['workshop_uid']) || 
                    empty($context['data']['datumstart']) || 
                    empty($context['data']['uhrzeitstart']) || 
                    empty($context['data']['uhrzeitend'])) {
                    return true; // Let other validators handle empty fields
                }

                $conditions = [
                    $this->aliasField('workshop_uid') => $context['data']['workshop_uid'],
                    $this->aliasField('datumstart') => $context['data']['datumstart'],
                    $this->aliasField('uhrzeitstart') => $context['data']['uhrzeitstart'],
                    $this->aliasField('uhrzeitend') => $context['data']['uhrzeitend'],
                    $this->aliasField('status IN') => [APP_ON, APP_OFF],
                ];

                // When editing an existing event, exclude it from the duplicate check
                if (!empty($context['data']['uid'])) {
                    $conditions[$this->aliasField('uid !=')] = $context['data']['uid'];
                }

                $duplicateCount = $this->find('all', conditions: $conditions)->count();
                
                return $duplicateCount === 0;
            },
            'message' => 'Es existiert bereits ein Termin für diese Initiative zur gleichen Zeit an diesem Tag.'
        ]);
        
        return $validator;
    }

    public function getKeywordSearchConditions(string $keyword, bool $negate): mixed
    {
        $changeableOrConditions = [
            'Workshops.name LIKE' => "%{$keyword}%",
            'Events.ort LIKE' => "%{$keyword}%",
        ];

        $fixedOrConditions = [
            'Events.zip LIKE' => "{$keyword}%",
        ];

        $changeableOrConditions = $this->getChangeableOrConditions($keyword, $changeableOrConditions);
        $orConditions = array_merge($changeableOrConditions, $fixedOrConditions);

        return function ($exp, $query) use ($orConditions, $negate) {
            $result = $exp->or($orConditions);
            if ($negate) {
                $result = $exp->not($result);
            }
            return $result;
        };
    }

    /**
     * @return array<int, int>
     */
    public function getProvinceCounts(): array
    {
        
        $query = $this->find('all')
        ->select([
            'province_id',
            'count' => $this->find()->func()->count('*')
        ])
        ->leftJoinWith('Workshops')
        ->where($this->getListConditions())
        ->groupBy($this->aliasField('province_id'));
        $provinces = $query->toArray();

        $provincesMap = [];
        foreach($provinces as $province) {
            $provincesMap[$province->province_id] = $province->count;
        }
        return $provincesMap;
    }    

    /**
     * @return array<int|string, string|int>
     */
    public function getListConditions(): array
    {
        return [
            'Events.status' => APP_ON,
            'Workshops.status' => APP_ON,
            'DATE(Events.datumstart) >= DATE(NOW())'
        ];
    }

    /**
     * @return array<int|string, string>
     */
    public function getListFields(): array
    {
        return [
            'Events.uid',
            'Events.lat',
            'Events.lng',
            'Events.datumstart',
            'Events.uhrzeitstart',
            'Events.uhrzeitend',
            'Events.strasse',
            'Events.zip',
            'Events.ort',
            'Events.image',
            'Events.image_alt_text',
            'Events.owner',
            'Events.eventbeschreibung',
            'Events.is_online_event',
            'Workshops.name',
            'Workshops.url',
            'Workshops.image',
            'uniquePlace' => 'MD5(Events.lat * Events.lng)', // create unique lat/lng based field for combining events with same place
            'directurl' => "CONCAT(Workshops.url, '?event=', Events.uid, ',', Events.datumstart)",
        ];
    }

    /**
     * @return array<string, string>
     */
    public function getListOrder(): array
    {
        return [
            'Events.datumstart' => 'ASC',
            'Events.uhrzeitstart' => 'ASC'
        ];
    }

    public function findAll(SelectQuery $query): SelectQuery
    {
        return $query->formatResults(function (CollectionInterface $results): CollectionInterface {

            return $results->map(function (Event $row): Event {

                if ($row['datumstart']) {
                    $row['datumstart_formatted'] = $row['datumstart']->i18nFormat(Configure::read('DateFormat.Database'));
                }

                if ($row['uhrzeitstart']) {
                    $row['uhrzeitstart_formatted'] = $row['uhrzeitstart']->i18nFormat(Configure::read('DateFormat.de.TimeShort'));
                }
                if ($row['uhrzeitend']) {
                    $row['uhrzeitend_formatted'] = $row['uhrzeitend']->i18nFormat(Configure::read('DateFormat.de.TimeShort'));
                }

                return $row;

            });

        });
    }

}

?>
