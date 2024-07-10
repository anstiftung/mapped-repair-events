<?php

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\Validation\Validator;
use App\Model\Traits\SearchExceptionsTrait;
use App\Services\GeoService;
use Cake\ORM\Query\SelectQuery;
use Cake\I18n\DateTime;

class EventsTable extends AppTable
{

    use SearchExceptionsTrait;

    public $name_de = 'Termin';

    public $allowedBasicHtmlFields = [
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
    }

    public function validationDefault(Validator $validator): \Cake\Validation\Validator
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
        $validator->equals(0, $invalidCoordinateMessage);
        $validator->equals(0, $invalidCoordinateMessage);
        $validator->notEmptyString('workshop_uid', 'Bitte wähle eine Initiative aus.');
        $validator->notEmptyString('ort', 'Bitte trage die Stadt ein.');
        $validator->minLength('ort', 2, 'Bitte trage die Stadt ein.');
        $validator->notEmptyString('strasse', 'Bitte trage die Straße ein.');
        $validator->minLength('strasse', 2, 'Bitte trage die Straße ein.');
        $validator->notEmptyString('zip', 'Bitte trage die PLZ ein.');
        $validator->decimal('lat', null, 'Bitte gib eine Zahl ein.');
        $validator->decimal('lng', null, 'Bitte gib eine Zahl ein.');
        $validator->add('zip', 'validFormat', [
            'rule' => array('custom', ZIP_REGEX),
            'message' => 'Die PLZ ist nicht gültig.'
        ]);
        return $validator;
    }

    public function getKeywordSearchConditions($keyword, $negate) {

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

    public function getProvinceCounts() {
        
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

    public function getListConditions() {
        return [
            'Events.status' => APP_ON,
            'Workshops.status' => APP_ON,
            'DATE(Events.datumstart) >= DATE(NOW())'
        ];
    }

    public function getListFields() {
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
            'directurl' => "CONCAT(Workshops.url, '?event=', Events.uid, ',', Events.datumstart)"
        ];
    }

    public function getListOrder() {
        return [
            'Events.datumstart' => 'ASC',
            'Events.uhrzeitstart' => 'ASC'
        ];
    }

    public function findAll(SelectQuery $query): SelectQuery
    {
        return $query->formatResults(function (\Cake\Collection\CollectionInterface $results) {

            return $results->map(function ($row) {

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
