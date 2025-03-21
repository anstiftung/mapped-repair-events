<?php
declare(strict_types=1);
namespace Admin\Controller;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\ORM\Query\SelectQuery;
use Cake\Utility\Inflector;
use Cake\ORM\Entity;
use Cake\Datasource\EntityInterface;

class AdminAppController extends AppController
{

    public array $paginate;

    public array $searchOptions = [];

    public bool $searchName = true;

    public bool $searchText = true;

    public bool $searchUid = true;

    public bool $searchStatus = true;

    public array $conditions = [];

    public array $afterFindCallbacks = [];

    public array $matchings = [];

    public function initialize(): void
    {
        parent::initialize();
        $this->paginate = [
            'limit' => 200,
            'maxLimit' => 100000
        ];
    }

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        $title = $this->name;
        if ($this->request->getParam('action') != 'index') {
            $title .= ' | ' . Inflector::camelize($this->request->getParam('action'));
        }
        $metaTags = [
            'title' => $title
        ];
        $this->set('metaTags', $metaTags);

        if ($this->searchText) {
            $this->searchOptions[$this->pluralizedModelName . '.text'] = [
                'name' => 'Text',
                'searchType' => 'search'
            ];
        }
        if ($this->searchName) {
            $this->searchOptions[$this->pluralizedModelName . '.name'] = [
                'name' => 'Name',
                'searchType' => 'search'
            ];
        }
        if ($this->searchUid) {
            $this->searchOptions[$this->pluralizedModelName . '.uid'] = [
                'name' => 'UID',
                'searchType' => 'equal'
            ];
        }

        if ($this->searchStatus) {
            $this->searchOptions[$this->pluralizedModelName . '.status'] = [
                'name' => $this->pluralizedModelName . '.status',
                'searchType' => 'equal',
                'extraDropdown' => true
            ];
            $this->generateSearchConditions('status');
        }
        $this->searchOptions = array_reverse($this->searchOptions);

        $this->generateSearchConditions('standard');

        $this->prepareSearchOptionsForDropdown();
    }

    public function index(): void
    {

        $this->paginate['order'] = [
            $this->pluralizedModelName . '.updated' => 'DESC'
        ];
        if ($this->searchStatus) {
            $this->conditions[] = $this->pluralizedModelName . '.status > ' . APP_DELETED;
        }
        $this->set('objectClass', Inflector::classify($this->name));

        $this->set('searchStatus', $this->searchStatus);
    }

    protected function generateSearchConditions(string $searchFieldKey): null
    {
        $queryParams = $this->request->getQueryParams();

        if (isset($queryParams['val-' . $searchFieldKey])) {
            $filterValue = $queryParams['val-' . $searchFieldKey];
            if ($filterValue == '') {
                return null;
            }
            $key = isset($this->searchOptions[$queryParams[
                'key-' . $searchFieldKey
            ]]);
            if ($key) {
                $searchOption = $this->searchOptions[
                    $queryParams['key-' . $searchFieldKey]
                ];
                $searchType = $searchOption['searchType'];
                $negate = $searchOption['negate'] ?? false;
            } else {
                $this->AppFlash->setFlashError('Bitte wähle im Dropdown ein Suchfeld aus.');
                return null;
            }
            switch ($searchType) {
                case 'equal':
                    $searchValue = $queryParams['val-' . $searchFieldKey];
                    if (strpos($queryParams['key-' . $searchFieldKey], '.uid') !== false) {
                        $searchValue = (int) $searchValue;
                    }
                    $this->conditions[$queryParams['key-' . $searchFieldKey]] = $searchValue;
                    break;
                case 'search':
                    $condition = $queryParams['key-' . $searchFieldKey] . " LIKE '%" . $queryParams['val-' . $searchFieldKey] . "%'";
                    if ($negate) {
                        $condition = 'NOT (' . $condition . ')';
                    }
                    $this->conditions[] = $condition;
                    break;
                case 'matching':
                    $this->matchings[] = [
                        'association' => $this->searchOptions[$queryParams['key-' . $searchFieldKey]]['association'],
                        'condition' => [
                            $queryParams['key-' . $searchFieldKey] => $queryParams['val-' . $searchFieldKey]
                        ]
                    ];
                    break;
                case 'custom':
                    $customCondition = $searchOption['conditions'][$queryParams['val-' . $searchFieldKey]];
                    if (is_string($customCondition)) {
                        $this->conditions[] = $customCondition;
                    } else {
                        $this->afterFindCallbacks[] = $customCondition;
                    }
                    break;

            }
        }
        return null;
    }

    protected function addMatchingsToQuery(SelectQuery $query): SelectQuery
    {

        foreach($this->matchings as $matching) {
            $query->matching($matching['association'], function(SelectQuery $q) use ($matching) {
                return $q->where($matching['condition']);
            });
        }
        return $query;
    }

    protected function saveObject(EntityInterface $entity): void
    {
        $modelName = $this->modelName;
        $entity = $this->stripTagsFromFields($entity, $modelName);
        if ($this->$modelName->save($entity)) {
            $this->AppFlash->setFlashMessage($this->$modelName->name_de . ' erfolgreich gespeichert.');
            $this->redirect($this->getPreparedReferer());
        } else {
            $this->AppFlash->setFlashError($this->$modelName->name_de . ' wurde <b>nicht</b> gespeichert. Bitte überprüfe das Formular.');
        }
    }

    protected function addSearchOptions(array $searchOptions): void
    {
        $searchOptions = array_reverse($searchOptions);
        if (empty($this->searchOptions)) {
            $this->searchOptions = $searchOptions;
        } else {
            $this->searchOptions = array_merge($this->searchOptions, $searchOptions);
        }
        $this->prepareSearchOptionsForDropdown();
    }

    private function prepareSearchOptionsForDropdown(): void
    {
        $searchOptionsForDropdown = $this->searchOptions;
        foreach ($searchOptionsForDropdown as $key => $searchOption) {
            if (isset($searchOption['extraDropdown']) && $searchOption['extraDropdown']) {
                unset($searchOptionsForDropdown[$key]);
            }
        }
        $this->set('searchOptionsForDropdown', $searchOptionsForDropdown);
    }



}
