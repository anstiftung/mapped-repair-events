<?php
declare(strict_types=1);
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Utility\Inflector;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Authentication\IdentityInterface;
use ArrayAccess;
use Cake\ORM\TableRegistry;

class User extends Entity implements IdentityInterface
{
    
    const STATUS_OK = 'ok';
    
    protected array $_virtual = ['name'];

    public function __construct(array $properties = [], array $options = [])
    {
        parent::__construct($properties, $options);
        $this->privatizeData($this);
    }

    public function getIdentifier(): string|int|null
    {
        return $this->get('uid');
    }

    /**
     * @return ArrayAccess<string, mixed>|array<string, mixed>
     */
    public function getOriginalData(): ArrayAccess|array
    {
        $this->revertPrivatizeData();
        return $this;
    }

    public function isAdmin(): bool
    {
        $groupsTable = TableRegistry::getTableLocator()->get('Groups');
        return $groupsTable->isAdmin($this);
    }

    public function isOrga(): bool
    {
        $groupsTable = TableRegistry::getTableLocator()->get('Groups');
        return $groupsTable->isOrga($this);
    }

    public function isRepairhelper(): bool
    {
        $groupsTable = TableRegistry::getTableLocator()->get('Groups');
        return $groupsTable->isRepairhelper($this);
    }

    /**
     * diese methode ist für das frontend (keine uid in url vorhanden)
     * checks if the logged user is the owner of the passed modelName / url or not
     */
    public function isOwnerByModelNameAndUrl(string $modelName, string $url): bool
    {

        $pluralizedModelName = Inflector::pluralize($modelName);
        $objectTable = TableRegistry::getTableLocator()->get($pluralizedModelName);
        $object = $objectTable->find('all',
            conditions: [
                $pluralizedModelName.'.owner' => $this->get('uid'),
                $pluralizedModelName.'.url' => $url,
                $pluralizedModelName.'.status >= '.APP_DELETED,
            ],
        );

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
        $rootTable = TableRegistry::getTableLocator()->get('Roots');
        $objectType = $rootTable->getType($uid);
        $objectClass = Inflector::classify($objectType);
        $pluralizedClass = Inflector::pluralize($objectClass);
        $objectTable = TableRegistry::getTableLocator()->get($pluralizedClass);

        $object = $objectTable->find('all',
            conditions: [
                $pluralizedClass.'.owner' => $this->get('uid'),
                $pluralizedClass.'.uid' => $uid,
                $pluralizedClass.'.status >= '.APP_DELETED,
            ],
        );

        if ($object->count() == 1) {
            return true;
        }
        return false;

    }

    public function revertPrivatizeData(): void
    {
        foreach($this->extractOriginalChanged($this->getVisible()) as $property => $value) {
            $this->$property = $value;
        }
    }

    public function privatizeData(User &$user): void
    {
        if (!is_null($user->private)) {
            $privateFields = explode(',',  $user->private);
            $privateFields = str_replace('-', '_', $privateFields);
            foreach($user->getVisible() as $property) {
                if (in_array($property, $privateFields)) {
                    $user->$property = null;
                }
            }
        }
    }

    protected function _getName(): string
    {
        $names = [];
        if (isset($this->_fields['firstname'])) {
            $names[] = $this->_fields['firstname'];
        }
        if (isset($this->_fields['lastname'])) {
            $names[] = $this->_fields['lastname'];
        }
        return join(' ', $names);
    }

    protected function _setPassword(string $password): string
    {
        if (strlen($password) > 0) {
            return (new DefaultPasswordHasher)->hash($password);
        }
        return '';
    }

    protected array $_hidden = [
        'password',
        '_joinData',
        'UsersWorkshops'
    ];

}
