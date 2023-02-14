<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Utility\Inflector;
use Cake\Datasource\FactoryLocator;
use Cake\Auth\DefaultPasswordHasher;
use Authentication\IdentityInterface;

class User extends Entity implements IdentityInterface
{

    protected $_virtual = ['name'];

    public function __construct(array $properties = [], array $options = [])
    {
        parent::__construct($properties, $options);
        $this->privatizeData($this);
    }

    public function getIdentifier()
    {
        return $this->id;
    }

    public function getOriginalData()
    {
        $this->revertPrivatizeData();
        return $this;
    }

    public function isAdmin(): bool
    {
        $group = FactoryLocator::get('Table')->get('Groups');
        return $group->isAdmin($this);
    }

    public function isOrga(): bool
    {
        $group = FactoryLocator::get('Table')->get('Groups');
        return $group->isOrga($this);
    }

    public function isRepairhelper(): bool
    {
        $group = FactoryLocator::get('Table')->get('Groups');
        return $group->isRepairhelper($this);
    }

    public function IsInGroup($groups): bool
    {
        $group = FactoryLocator::get('Table')->get('Groups');
        return $group->isInGroup($this, $groups);
    }

    /**
     * diese methode ist für das frontend (keine uid in url)
     * checks if the logged user is the owner of the passed modelName / url or not
     * @param string $modelName
     * @param string url
     */
    public function isOwnerByModelNameAndUrl($modelName, $url): bool
    {

        $pluralizedModelName = Inflector::pluralize($modelName);
        $objectTable = FactoryLocator::get('Table')->get($pluralizedModelName);
        $object = $objectTable->find('all', [
            'conditions' => [
                $pluralizedModelName.'.owner' => $this->uid,
                $pluralizedModelName.'.url' => $url,
                $pluralizedModelName.'.status >= '.APP_DELETED,
        ]]);

        if ($object->count() == 1) {
            return true;
        }

        return false;

    }

  /**
   * diese methode ist für den admin (uid in url)
   */
    public function isOwner(int $uid): bool
    {
        $rootTable = FactoryLocator::get('Table')->get('Roots');
        $objectType = $rootTable->getType($uid);
        $objectClass = Inflector::classify($objectType);
        $pluralizedClass = Inflector::pluralize($objectClass);
        $objectTable = FactoryLocator::get('Table')->get($pluralizedClass);

        $object = $objectTable->find('all', [
            'conditions' => [
                $pluralizedClass.'.owner' => $this->uid,
                $pluralizedClass.'.uid' => $uid,
                $pluralizedClass.'.status >= '.APP_DELETED,
        ]]);

        if ($object->count() == 1) {
            return true;
        }
        return false;

    }

    public function revertPrivatizeData()
    {
        foreach($this->extractOriginalChanged($this->getVisible()) as $property => $value) {
            $this->$property = $value;
        }
    }

    public function privatizeData(&$user)
    {

        if (is_null($user->private)) {
            return;
        }

        $privateFields = explode(',',  $user->private);
        $privateFields = str_replace('-', '_', $privateFields);
        foreach($user->getVisible() as $property) {
            if (in_array($property, $privateFields)) {
                $user->$property = null;
            }
        }

    }

    protected function _getName()
    {
        if (isset($this->_fields['firstname']) && isset($this->_fields['lastname'])) {
            return $this->_fields['firstname'] . ' ' . $this->_fields['lastname'];
        }
        return '';
    }

    protected function _setPassword($password)
    {
        if (strlen($password) > 0) {
            return (new DefaultPasswordHasher)->hash($password);
        }
    }

    /**
     * Fields that are excluded from JSON versions of the entity.
     * @var array
     */
    protected $_hidden = [
        'password',
        '_joinData',
        'UsersWorkshops'
    ];

}
