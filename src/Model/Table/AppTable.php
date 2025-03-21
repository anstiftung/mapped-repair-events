<?php
declare(strict_types=1);
namespace App\Model\Table;

use App\Controller\Component\StringComponent;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Event\EventInterface;
use Cake\ORM\Table;
use Cake\Routing\Router;
use Cake\Validation\Validator;
use ArrayObject;

abstract class AppTable extends Table
{

    public array $allowedBasicHtmlFields = [];

    public ?int $loggedUserUid = 0;

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

    public function addUrlValidation(Validator $validator): Validator
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

    public function validationAdmin(Validator $validator): Validator
    {
        $validator = $this->addUrlValidation($validator);
        return $validator;
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator = $this->validationAdmin($validator);
        return $validator;
    }

    public function getNumberRangeValidator(Validator $validator, string $field, int $min, int $max): Validator
    {
        $message = 'Die Eingabe muss eine Zahl zwischen ' . $min . ' und ' . $max . ' sein.';
        $validator->lessThanOrEqual($field, $max, $message);
        $validator->greaterThanOrEqual($field, $min, $message);
        $validator->notEmptyString($field, $message);
        return $validator;
    }

    public function getPatchedEntityForAdminEdit(EntityInterface $entity, array $data): EntityInterface
    {
        $isAdmin = Router::getRequest()?->getAttribute('identity')?->isAdmin();
        $patchedEntity = $this->patchEntity(
            $entity,
            $data,
            ['validate' => $isAdmin ? 'admin' : true] // calls Table::validationAdmin
        );
        return $patchedEntity;
    }

    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        $this->loggedUserUid = Router::getRequest()?->getAttribute('identity')?->uid;

        if ($entity->isNew()) {
            $rootsTable = FactoryLocator::get('Table')->get('Roots');
            $rootEntity = [
                'Roots' => [
                    'object_type' => $this->getTable()
                ]
            ];
            $result = $rootsTable->save($rootsTable->newEntity($rootEntity));
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
        }

        $entity->updated_by = $this->loggedUserUid;

    }

}
?>