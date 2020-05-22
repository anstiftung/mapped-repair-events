<?php
namespace App\Model\Table;

use App\Controller\Component\StringComponent;
use Cake\Event\EventInterface;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Validation\Validator;

abstract class AppTable extends Table
{

    public $allowedBasicHtmlFields = [];

    public $Root;

    public $loggedUserUid = 0;

    public function initialize(array $config): void
    {
        $this->setPrimaryKey('uid');

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created' => 'new',
                    'updated' => 'always' // cake uses "modified"
                ]
            ]
        ]);

        $this->belongsTo('Roots', [
            'foreignKey' => 'uid'
        ]);
        $this->hasOne('Metatags', [
            'foreignKey' => 'object_uid'
        ]);
        $this->belongsTo('CurrentlyUpdatedByUsers', [
            'className' => 'Users',
            'foreignKey' => 'currently_updated_by'
        ]);
        // do not call owner because the attached object "owner" would interfere with table field "owner"
        $this->belongsTo('OwnerUsers', [
            'className' => 'Users',
            'foreignKey' => 'owner'
        ]);

    }

    public function addUrlValidation(Validator $validator)
    {
        $validator->notEmptyString('url', 'Bitte trage einen Slug ein.');
        $validator->add('url', 'unique', [
            'rule' => 'validateUnique',
            'provider' => 'table',
            'message' => 'Dieser Slug wird bereits verwendet.'
        ]);
        $validator->add('url', 'alphaNumericDash', [
            'rule' => function ($value, $context) {
                return (boolean) preg_match('`^[0-9a-zA-Z-]*$`', $value);
            },
            'message' => 'Bitte nur a-z, Zahlen und das Zeichen - verwenden.'
        ]);
        return $validator;
    }

    public function validationAdmin(Validator $validator)
    {
        $validator = $this->addUrlValidation($validator);
        return $validator;
    }

    public function validationDefault(Validator $validator): \Cake\Validation\Validator
    {
        $validator = $this->validationAdmin($validator);
        return $validator;
    }

    public function getNumberRangeValidator(Validator $validator, $field, $min, $max)
    {
        $message = 'Die Eingabe muss eine Zahl zwischen ' . $min . ' und ' . $max . ' sein.';
        $validator->lessThanOrEqual($field, $max, $message);
        $validator->greaterThanOrEqual($field, $min, $message);
        $validator->notEmptyString($field, $message);
        return $validator;
    }

    public function getPatchedEntityForAdminEdit($entity, $data, $useDefaultValidation)
    {
        $patchedEntity = $this->patchEntity(
            $entity,
            $data,
            ['validate' => !$useDefaultValidation ? 'admin' : true] // calls Table::validationAdmin
        );
        return $patchedEntity;
    }

    public function beforeSave(EventInterface $event, $entity, $options)
    {

        $request = Router::getRequest();
        if ($request) {
            $session = $request->getSession();
            if ($session->read('Auth.User.uid') !== null) {
                $this->loggedUserUid = $session->read('Auth.User.uid');
            }
        }
        if ($entity->isNew()) {

            /*
             * INSERT
             */
            if (! $this->Root) {
                $this->Root = TableRegistry::getTableLocator()->get('Roots');
            }
            $rootEntity = [
                'Roots' => [
                    'object_type' => $this->getTable()
                ]
            ];
            $result = $this->Root->save($this->Root->newEntity($rootEntity));
            $entity->uid = $result->uid;

            if ($entity->url == '') {
                $entity->url = !empty($entity->name) ? StringComponent::slugify($entity->name) : $result->uid;
            }
            if ($entity->owner == '') {
                $entity->owner = $this->loggedUserUid;
            }
            if ($entity->status == '') {
                $entity->status = APP_OFF;
            }

        } else {
            /*
             * UPDATE
             */
        }

        // add default protocol to field website
        if ($this->hasField('website') && $entity->website != '') {
            if (!preg_match('/^https?:\/\//', $entity->website)) {
                $entity->website = 'http://'.$entity->website;
            }
        }

        $entity->updated_by = $this->loggedUserUid;

    }

}
?>