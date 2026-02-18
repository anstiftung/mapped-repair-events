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

    public function insert(): Response
    {
        $apiTokenData = [
            'name' => 'Neuer API Token',
            'token' => ApiToken::generateToken(),
            'type' => ApiToken::TYPE_WORKSHOPS,
            'allowed_search_terms' => [],
            'allowed_domains' => [],
            'status' => APP_OFF,
        ];

        $entity = $this->ApiToken->newEntity($apiTokenData);
        $apiToken = $this->ApiToken->save($entity);

        if (!$apiToken) {
            $this->AppFlash->setFlashError('API Token konnte nicht erstellt werden.');
            return $this->redirect($this->getReferer());
        }

        $this->AppFlash->setFlashMessage('API Token erfolgreich erstellt.');

        return $this->redirect($this->getReferer());
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

        if (!empty($this->request->getData())) {
            $data = $this->normalizeRequestData($this->request->getData());

            $apiToken = $this->ApiToken->patchEntity($apiToken, $data);

            if ($this->ApiToken->save($apiToken)) {
                $this->AppFlash->setFlashMessage('API Token erfolgreich aktualisiert.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->AppFlash->setFlashError('API Token konnte nicht gespeichert werden.');
            }
        }

        $apiToken->allowed_search_terms = $this->convertJsonArrayToTextareaValue($apiToken->allowed_search_terms);
        $apiToken->allowed_domains = $this->convertJsonArrayToTextareaValue($apiToken->allowed_domains);

        $this->set('apiToken', $apiToken);
        $this->set('id', $id);
        
        return null;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeRequestData(array $data): array
    {
        unset($data['token']);

        if (array_key_exists('allowed_search_terms', $data) && is_string($data['allowed_search_terms'])) {
            $data['allowed_search_terms'] = $this->convertTextareaValueToJsonArray($data['allowed_search_terms']);
        }

        if (array_key_exists('allowed_domains', $data) && is_string($data['allowed_domains'])) {
            $data['allowed_domains'] = $this->convertTextareaValueToJsonArray($data['allowed_domains']);
        }

        return $data;
    }

    private function convertTextareaValueToJsonArray(string $value): ?string
    {
        $entries = array_filter(array_map('trim', explode("\n", $value)));

        if ($entries === []) {
            return null;
        }

        return json_encode(array_values($entries), JSON_UNESCAPED_UNICODE);
    }

    private function convertJsonArrayToTextareaValue(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $entries = is_string($value)
            ? json_decode($value, true)
            : $value;

        if (!is_array($entries) || $entries === []) {
            return null;
        }

        return implode("\n", $entries);
    }

}
