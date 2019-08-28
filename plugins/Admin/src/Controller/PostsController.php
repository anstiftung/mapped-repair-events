<?php
namespace Admin\Controller;

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Date;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;

class PostsController extends AdminAppController
{

    public function __construct($request = null, $response = null)
    {
        parent::__construct($request, $response);
        $this->Post = TableRegistry::getTableLocator()->get('Posts');
        $this->Blog = TableRegistry::getTableLocator()->get('Blogs');
        $this->User = TableRegistry::getTableLocator()->get('Users');
    }

    public function insert($blogId)
    {
        
        // admin defaults
        $post = [
            'name' => 'Neuer Post von ' . $this->AppAuth->getUserName(),
            'publish' => Date::now(),
            'url' => StringComponent::createRandomString(6)
        ];
        
        
        if ($blogId != '') {
            $post['blog_id'] = $blogId;
        }
        $entity = $this->Post->newEntity($post);
        $post = $this->Post->save($entity);
        
        $this->AppFlash->setFlashMessage('Post erfolgreich erstellt. UID: ' . $post->uid); // uid for fixture
        $this->redirect($this->referer());
    }

    public function isAuthorized($user)
    {
        if ($this->request->getParam('action') == 'edit') {
            
            if (! $this->AppAuth->user()) {
                return false;
            }
            
            if ($this->AppAuth->isAdmin()) {
                $this->useDefaultValidation = false;
                return true;
            }
            
            $postUid = (int) $this->request->getParam('pass')[0];
            
            // NUR der owner des posts darf ihn bearbeiten
            if ($this->AppAuth->isBlog() && $this->AppAuth->isOwner($postUid)) {
                return true;
            }
            
            return false;
            
            // blog und admin user dürfen auf die index- bzw index-seite
        } elseif (in_array($this->request->getParam('action'), [
            'insert',
            'index'
        ])) {
            
            if (! $this->AppAuth->user()) {
                return false;
            }
            
            if ($this->AppAuth->isAdmin()) {
                $this->useDefaultValidation = false;
                return true;
            }
            
            if ($this->AppAuth->isBlog()) {
                return true;
            }
            
            return false;
        } else {
            return parent::isAuthorized($user);
        }
    }

    public function edit($uid)
    {
        
        if (empty($uid)) {
            throw new NotFoundException;
        }
        
        $post = $this->Post->find('all', [
            'conditions' => [
                'Posts.uid' => $uid,
                'Posts.status >= ' . APP_DELETED
            ],
            'contain' => [
                'Photos',
                'Metatags'
            ]
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
            $patchedEntity = $this->Post->getPatchedEntityForAdminEdit($post, $this->request->getData(), $this->useDefaultValidation);
            
            if (!($patchedEntity->hasErrors())) {
                $patchedEntity = $this->patchEntityWithCurrentlyUpdatedFields($patchedEntity);
                $this->saveObject($patchedEntity, $this->useDefaultValidation);
            } else {
                $post = $patchedEntity;
            }
        }
        
        $this->set('post', $post);
        $this->set('blogs', $this->Blog->getForDropdown());
        
    }

    public function beforeFilter(Event $event)
    {
        
        parent::beforeFilter($event);
        
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
                'extraDropdown' => true
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
    }

    public function index()
    {
        parent::index();
        
        // bei blog usern nur eigene posts anzeigen (spart das ausblenden des edit-symbols bei fremdcontent)
        $conditions = [
            'Posts.status > ' . APP_DELETED
        ];
        
        if ($this->useDefaultValidation) {
            $conditions['Posts.owner'] = $this->AppAuth->getUserUid();
        }
        
        $conditions = array_merge($this->conditions, $conditions);
        
        $query = $this->Post->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'OwnerUsers',
                'Blogs'
            ]
        ]);
        
        $objects = $this->paginate($query, [
            'order' => [
                'Posts.publish' => 'DESC'
            ]
        ]);
        $this->set('objects', $objects->toArray());
        $this->set('blogs', $this->Blog->getForDropdown());
        $this->set('users', $this->User->getForDropdown());
    }
}
    