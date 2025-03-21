<?php
declare(strict_types=1);
namespace Admin\Controller;

use App\Controller\Component\StringComponent;
use Cake\Http\Exception\NotFoundException;
use App\Model\Table\PagesTable;

class PagesController extends AdminAppController
{

    public PagesTable $Page;
    
    public function initialize(): void
    {
        parent::initialize();
        // keep that because of AppController::stripTagsFromFields()
        $this->Page = $this->getTableLocator()->get('Pages');
    }

    public function insert(): void
    {
        $page = [
            'name' => 'Neue Seite von ' . $this->loggedUser->name,
            'url' => StringComponent::createRandomString(6)
        ];
        $entity = $this->Page->newEntity($page);
        $page = $this->Page->save($entity);
        $this->AppFlash->setFlashMessage('Seite erfolgreich erstellt. UID: ' . $page->uid); // uid for fixture
        $this->redirect($this->getReferer());
    }

    public function edit(int $uid): void
    {
        $page = $this->Page->find('all',
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

        $this->set('pagesForSelect', $this->Page->getForSelect($page->uid));
        $this->set('uid', $page->uid);

        $this->setReferer();
        $this->setIsCurrentlyUpdated($uid);

        if (!empty($this->request->getData())) {

            $patchedEntity = $this->Page->getPatchedEntityForAdminEdit($page, $this->request->getData());
            if (!($patchedEntity->hasErrors())) {
                $patchedEntity = $this->patchEntityWithCurrentlyUpdatedFields($patchedEntity);
                $this->saveObject($patchedEntity);
            } else {
                $page = $patchedEntity;
            }
        }

        $this->set('page', $page);
    }

    public function index(): void
    {
        parent::index();
        $conditions = [
            'Pages.status > ' . APP_DELETED
        ];
        $conditions = array_merge($this->conditions, $conditions);

        $query = $this->Page->find('all',
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
