<?php

namespace App\Controller\Component;

use Cake\Controller\Component\AuthComponent;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;

class AppAuthComponent extends AuthComponent {
    
    public $components = ['AppFlash', 'Session', 'RequestHandler', 'AppEmail'];
    
    public $controller;
    
    public function flash($message): void
    {
        $this->AppFlash->setFlashError($message);
    }

    public function getGroupId() {
        return $this->user('group_id');
    }
    
    private function prepareGroupModel() {
        return TableRegistry::getTableLocator()->get('Groups');
    }
    
    public function isAdmin() {
        if (!$this->user()) return false;
        $group = $this->prepareGroupModel();
        return $group->isAdmin($this->user());
    }
    public function isRepairhelper() {
        if (!$this->user()) return false;
        $group = $this->prepareGroupModel();
        return $group->isRepairhelper($this->user());
    }
    public function IsOrga() {
        if (!$this->user()) return false;
        $group = $this->prepareGroupModel();
        return $group->isOrga($this->user());
    }
    public function IsInGroup($groups) {
        if (!$this->user()) return false;
        $group = $this->prepareGroupModel();
        return $group->isInGroup($this->user(), $groups);
    }
    
  /**
   * diese methode ist für das frontend (keine uid in url)
   * checks if the logged user is the owner of the passed modelName / url or not
   * @param string $modelName
   * @param string url
   */
  public function isOwnerByModelNameAndUrl($modelName, $url) {
    
    $pluralizedModelName = Inflector::pluralize($modelName);
    $objectTable = TableRegistry::getTableLocator()->get($pluralizedModelName);
    $object = $objectTable->find('all', [
      'conditions' => [
          $pluralizedModelName.'.owner' => $this->getUserUid(),
          $pluralizedModelName.'.url' => $url,
          $pluralizedModelName.'.status >= '.APP_DELETED
    ]]);
    
    if ($object->count() == 1) {
      return true;
    }
    
    return false;
    
  }

  /**
   * diese methode ist für den admin (uid in url)
   * checks if the logged user is the owner of the passed uid or not
   * @param int $uid
   */
  public function isOwner($uid) {
      
     $rootTable = TableRegistry::getTableLocator()->get('Roots');
     $objectType = $rootTable->getType($uid);
     $objectClass = Inflector::classify($objectType);
     $pluralizedClass = Inflector::pluralize($objectClass);
     $objectTable = TableRegistry::getTableLocator()->get($pluralizedClass);
     
     $object = $objectTable->find('all', [
      'conditions' => [
          $pluralizedClass.'.owner' => $this->getUserUid(),
          $pluralizedClass.'.uid' => $uid,
          $pluralizedClass.'.status >= '.APP_DELETED
    ]]);
     
    if ($object->count() == 1) {
      return true;
    }
    return false;
    
  }
      
      /**
       * @return int/boolean logged in User's uid or false if empty $this->User['uid']
       */
      public function getUserUid() {
          if (!$this->user()) return 0;
          return $this->user()['uid'];
      }
      
      public function getUserName() {
          if (!$this->user()) return '';
          return $this->user()['firstname'] . ' ' . $this->user()['lastname'];
      }
      
      public function getUserFirstname() {
          if (!$this->user()) return '';
          return $this->user()['firstname'];
      }
      
      public function getUserLastname() {
          if (!$this->user()) return '';
          return $this->user()['lastname'];
      }
      
      public function getUserEmail() {
          if (!$this->user()) return '';
          return $this->user()['email'];
      }
      
      public function getUserNick() {
          if (!$this->user()) return '';
          return $this->user()['nick'];
      }
      
      public function getUser() {
          if (!$this->user()) return 0;
          return $this->user();
      }    
    
}

?>