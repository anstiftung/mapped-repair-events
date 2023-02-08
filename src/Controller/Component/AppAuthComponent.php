<?php

namespace App\Controller\Component;

use Cake\Controller\Component\AuthComponent;
use Cake\Datasource\FactoryLocator;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;

class AppAuthComponent extends AuthComponent {

    public $components = ['AppFlash', 'Session', 'RequestHandler', 'AppEmail'];

    public $controller;

    /**
     * also private data of logged user is needed sometimes (internally)
     */
    public function user(?string $key = null)
    {
        $userFromSession = parent::user();

        if (!empty($userFromSession)) {

            $userModel = FactoryLocator::get('Table')->get('Users');
            $user = $userModel->find('all', [
                'conditions' => [
                    'Users.uid' => $userFromSession['uid'],
                    'Users.status > ' . APP_DELETED
                ],
                'contain' => [
                    'Groups',
                    'Categories',
                    'Skills'
                ]
            ])->first();
            $user->revertPrivatizeData();

            if ($key === null) {
                return $user;
            }

            return Hash::get($user, $key);

        }

        return null;

    }

    private function prepareGroupModel() {
        return FactoryLocator::get('Table')->get('Groups');
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
    $objectTable = FactoryLocator::get('Table')->get($pluralizedModelName);
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

     $rootTable = FactoryLocator::get('Table')->get('Roots');
     $objectType = $rootTable->getType($uid);
     $objectClass = Inflector::classify($objectType);
     $pluralizedClass = Inflector::pluralize($objectClass);
     $objectTable = FactoryLocator::get('Table')->get($pluralizedClass);

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

}

?>