<?php
declare(strict_types=1);
namespace Admin\Controller;

use App\Controller\Component\StringComponent;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;

class PagesController extends AdminAppController
{

    public function insert(): Response
    {
        $page = [
            'name' => 'Neue Seite von ' . $this->loggedUser->name,
            'url' => StringComponent::createRandomString(6)
        ];
        /** @var \App\Model\Table\PagesTable */
        $pagesTable = $this->getTableLocator()->get('Pages');
        $entity = $pagesTable->newEntity($page);
        $page = $pagesTable->save($entity);
        $this->AppFlash->setFlashMessage('Seite erfolgreich erstellt. UID: ' . $page->uid); // uid for fixture
        return $this->redirect($this->getReferer());
    }

    public function edit(int $uid): ?Response
    {
        /** @var \App\Model\Table\PagesTable */
        $pagesTable = $this->getTableLocator()->get('Pages');
        $page = $pagesTable->find('all',
        conditions: [
            'Pages.uid' => $uid,
            'Pages.status >= ' . APP_DELETED
        ],
        contain: [
            'Metatags'
        ])->first();

        if (empty($page)) {
            throw new NotFoundException;
        }

        $this->set('pagesForSelect', $pagesTable->getForSelect($page->uid));
        $this->set('uid', $page->uid);

        $this->setReferer();
        $this->setIsCurrentlyUpdated($uid);

        if (!empty($this->request->getData())) {

            $patchedEntity = $pagesTable->getPatchedEntityForAdminEdit($page, $this->request->getData());
            if (!($patchedEntity->hasErrors())) {
                $patchedEntity = $this->patchEntityWithCurrentlyUpdatedFields($patchedEntity);
                return $this->saveObject($patchedEntity);
            } else {
                $page = $patchedEntity;
            }
        }

        $this->set('page', $page);
        return null;
    }

    public function index(): void
    {
        parent::index();
        $conditions = [
            'Pages.status > ' . APP_DELETED
        ];
        $conditions = array_merge($this->conditions, $conditions);

        /** @var \App\Model\Table\PagesTable */
        $pagesTable = $this->getTableLocator()->get('Pages');
        $query = $pagesTable->find('all',
        conditions: $conditions,
        contain: [
            'OwnerUsers',
            'ParentPages'
        ]);
        $objects = $this->paginate($query, [
            'order' => [
                'Pages.menu_type' => 'ASC',
                'Pages.position' => 'ASC',
                'ParentPages.menu_type' => 'ASC',
                'ParentPages.position' => 'ASC',
            ]
        ]);
        foreach($objects as $object) {
            if ($object->owner_user) {
                $object->owner_user->revertPrivatizeData();
            }
        }
        $this->set('objects', $objects);
    }
}
