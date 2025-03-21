<?php
declare(strict_types=1);
namespace Admin\Controller;

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use App\Model\Table\PostsTable;
use App\Model\Table\BlogsTable;
use App\Model\Table\UsersTable;
use Cake\I18n\Date;

class PostsController extends AdminAppController
{

    public PostsTable $Post;
    public BlogsTable $Blog;
    public UsersTable $User;

    public function initialize(): void
    {
        parent::initialize();
        // keep that because of AppController::stripTagsFromFields()
        $this->Post = $this->getTableLocator()->get('Posts');
        $this->Blog = $this->getTableLocator()->get('Blogs');
        $this->User = $this->getTableLocator()->get('Users');
    }

    public function insert(int $blogId): void
    {
        $post = [
            'name' => 'Neuer Post von ' . $this->loggedUser->name,
            'publish' => date('Y-m-d'),
            'url' => StringComponent::createRandomString(6),
            'blog_id' => $blogId,
        ];

        $entity = $this->Post->newEntity($post);
        $post = $this->Post->save($entity);

        $this->AppFlash->setFlashMessage('Post erfolgreich erstellt. UID: ' . $post->uid); // uid for fixture
        $this->redirect($this->getReferer());

    }

    public function edit(int $uid): void
    {
        $post = $this->Post->find('all',
        conditions: [
            'Posts.uid' => $uid,
            'Posts.status >= ' . APP_DELETED
        ],
        contain: [
            'Photos',
            'Metatags'
        ])->first();

        $photos = array();
        foreach($post->photos as $photo) {
            $photo->src = Configure::read('AppConfig.htmlHelper')->getThumbs800ImageMultiple($photo->name);
            $photos[] = $photo;
        }
        $this->set('photos', $photos);

        if (empty($post)) {
            throw new NotFoundException;
        }

        $this->set('uid', $uid);

        $this->setReferer();
        $this->setIsCurrentlyUpdated($uid);

        if (!empty($this->request->getData())) {

            if ($this->request->getData('Posts.publish')) {
                $this->request = $this->request->withData('Posts.publish', new Date($this->request->getData('Posts.publish')));
            }
            $patchedEntity = $this->Post->getPatchedEntityForAdminEdit($post, $this->request->getData());

            if (!($patchedEntity->hasErrors())) {
                $patchedEntity = $this->patchEntityWithCurrentlyUpdatedFields($patchedEntity);
                $this->saveObject($patchedEntity);
            } else {
                $post = $patchedEntity;
            }
        }

        $this->set('post', $post);
        $this->set('blogs', $this->Blog->getForDropdown());

    }

    public function beforeFilter(EventInterface $event): void
    {

        $this->addSearchOptions([
            'Posts.city' => [
                'name' => 'Posts.city',
                'searchType' => 'search'
            ],
            'Posts.author' => [
                'name' => 'Posts.author',
                'searchType' => 'search'
            ],
            'Posts.blog_id' => [
                'name' => 'Posts.blog_id',
                'searchType' => 'equal',
                'extraDropdown' => true,
            ],
            'Posts.owner' => [
                'name' => 'Posts.owner',
                'searchType' => 'equal',
                'extraDropdown' => true
            ]
        ]);

        // für optional dropdown
        $this->generateSearchConditions('opt-1');
        $this->generateSearchConditions('opt-2');

        parent::beforeFilter($event);

    }

    public function index(): void
    {
        parent::index();

        // bei blog usern nur eigene posts anzeigen (spart das ausblenden des edit-symbols bei fremdcontent)
        $conditions = [
            'Posts.status > ' . APP_DELETED
        ];

        $conditions = array_merge($this->conditions, $conditions);

        $query = $this->Post->find('all',
        conditions: $conditions,
        contain: [
            'OwnerUsers',
            'Blogs'
        ]);

        $objects = $this->paginate($query, [
            'order' => [
                'Posts.publish' => 'DESC'
            ]
        ]);
        foreach($objects as $object) {
            if ($object->owner_user) {
                $object->owner_user->revertPrivatizeData();
            }
        }
        $this->set('objects', $objects);
        $this->set('blogs', $this->Blog->getForDropdown());
        $this->set('users', $this->User->getForDropdown());
    }
}
