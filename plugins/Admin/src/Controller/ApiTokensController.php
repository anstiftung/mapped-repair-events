<?php
declare(strict_types=1);
namespace Admin\Controller;

use Cake\Event\EventInterface;
use App\Model\Table\ApiTokensTable;
use App\Model\Entity\ApiToken;
use Cake\Http\Response;
use Cake\Http\Exception\NotFoundException;

class ApiTokensController extends AdminAppController
{
    public ApiTokensTable $ApiToken;

    public bool $searchName = true;
    public bool $searchText = false;
    public bool $searchUid = false;
    public bool $searchStatus = false;

    public function initialize(): void
    {
        parent::initialize();
        $this->ApiToken = $this->getTableLocator()->get('ApiTokens');
    }

    public function beforeFilter(EventInterface $event): void
    {
        $this->addSearchOptions([
            'ApiTokens.name' => [
                'name' => 'Name',
                'searchType' => 'search',
            ],
            'ApiTokens.status' => [
                'name' => 'Status',
                'searchType' => 'equal',
                'extraDropdown' => true,
            ],
        ]);

        $this->generateSearchConditions('standard');

        parent::beforeFilter($event);
    }

    public function index(): void
    {
        parent::index();

        $conditions = array_merge($this->conditions, []);

        $query = $this->ApiToken->find('all', conditions: $conditions);

        $objects = $this->paginate($query, [
            'order' => [
                'ApiTokens.created' => 'DESC',
            ],
        ]);

        $this->set('objects', $objects);
    }

    public function add(): ?Response
    {
        $apiToken = $this->ApiToken->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            // Generate a new token
            if (empty($data['token'])) {
                $data['token'] = ApiToken::generateToken();
            }

            // Convert allowed_search_terms from textarea to JSON array
            if (!empty($data['allowed_search_terms']) && is_string($data['allowed_search_terms'])) {
                $terms = array_filter(array_map('trim', explode("\n", $data['allowed_search_terms'])));
                $data['allowed_search_terms'] = json_encode(array_values($terms), JSON_UNESCAPED_UNICODE);
            }

            $apiToken = $this->ApiToken->patchEntity($apiToken, $data);

            if ($this->ApiToken->save($apiToken)) {
                $this->AppFlash->setFlashMessage('API Token erfolgreich erstellt: ' . $data['token'] . ' (Bitte speichern Sie dieses Token, es wird nicht erneut angezeigt!)');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->AppFlash->setFlashError('API Token konnte nicht gespeichert werden.');
            }
        }

        $this->set('apiToken', $apiToken);
        $this->setReferer();
        
        return null;
    }

    public function edit(int $id): ?Response
    {
        $apiToken = $this->ApiToken->find('all', conditions: [
            'ApiTokens.id' => $id,
        ])->first();

        if (empty($apiToken)) {
            throw new NotFoundException('API Token nicht gefunden');
        }

        $this->setReferer();

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            
            // Prevent token modification in edit
            unset($data['token']);

            // Convert allowed_search_terms from textarea to JSON array
            if (isset($data['allowed_search_terms']) && is_string($data['allowed_search_terms'])) {
                $terms = array_filter(array_map('trim', explode("\n", $data['allowed_search_terms'])));
                $data['allowed_search_terms'] = json_encode(array_values($terms), JSON_UNESCAPED_UNICODE);
            }

            $apiToken = $this->ApiToken->patchEntity($apiToken, $data);

            if ($this->ApiToken->save($apiToken)) {
                $this->AppFlash->setFlashMessage('API Token erfolgreich aktualisiert.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->AppFlash->setFlashError('API Token konnte nicht gespeichert werden.');
            }
        }

        // Convert JSON array to newline-separated string for textarea
        if (!empty($apiToken->allowed_search_terms)) {
            $terms = is_string($apiToken->allowed_search_terms) 
                ? json_decode($apiToken->allowed_search_terms, true) 
                : $apiToken->allowed_search_terms;
            if (is_array($terms)) {
                $apiToken->allowed_search_terms = implode("\n", $terms);
            }
        }

        $this->set('apiToken', $apiToken);
        $this->set('id', $id);
        
        return null;
    }

    public function regenerate(int $id): Response
    {
        $this->request->allowMethod(['post']);

        $apiToken = $this->ApiToken->get($id);
        $newToken = ApiToken::generateToken();
        $apiToken->token = $newToken;

        if ($this->ApiToken->save($apiToken)) {
            $this->AppFlash->setFlashMessage('Token erfolgreich neu generiert.');
            $this->Flash->success('Neues Token: ' . $newToken . ' (Bitte speichern Sie dieses Token!)');
        } else {
            $this->AppFlash->setFlashError('Token konnte nicht neu generiert werden.');
        }

        return $this->redirect(['action' => 'edit', $id]);
    }
}
