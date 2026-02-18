<?php
declare(strict_types=1);
namespace App\Controller;

use App\Model\Table\WorkshopsTable;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\View\JsonView;

class ApiController extends AppController
{

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authentication->disableIdentityCheck();
    }

    public function initialize(): void
    {
        parent::initialize();
        $this->addViewClasses([JsonView::class]);
        $this->request = $this->request->withParam('_ext', 'json');
    }

    public function getWorkshopsWithCityFilter(): ?Response
    {

        /* @phpstan-ignore-next-line */
        $this->response = $this->response->cors($this->request)
            ->allowOrigin(['*'])
            ->allowMethods(['GET'])
            ->build();

        $city = $this->request->getQuery('city');
        if ($city === null || strlen((string) $city) < 3) {
            return $this->response->withStatus(400)->withType('json')->withStringBody(json_encode('city not passed or invalid (min 3 chars)'));
        }

        /** @var WorkshopsTable $workshopsTable */
        $workshopsTable = $this->getTableLocator()->get('Workshops');
        $workshops = $workshopsTable->find('all',
        conditions: [
            'Workshops.status' => APP_ON,
            'Workshops.city LIKE' => "{$city}%",
        ],
        contain: [
            'Categories' => [
                'sort' => [
                    'Categories.name' => 'asc',
                ]
            ],
            'Events' => function($q) {
                return $q->where([
                    'DATE_FORMAT(Events.datumstart, \'%Y-%m-%d\') >= DATE_FORMAT(NOW(), \'%Y-%m-%d\')',
                ]);
            },
        ],
        order: ['Workshops.name' => 'asc']);

        if ($workshops->count() == 0) {
            return $this->response->withStatus(404)->withType('json')->withStringBody(json_encode('no workshops found'));
        }

        $preparedWorkshops = [];
        foreach($workshops as $workshop) {

            $preparedCategories = [];
            foreach($workshop->categories as $category) {
                $preparedCategories[] = [
                    'id' => $category->id,
                    'label' => html_entity_decode((string) $category->name),
                    'iconUrl' => Configure::read('AppConfig.serverName') . '/img/icon-skills/' . $category->icon . '.png',
                ];
            }

            $nextEventDate = null;
            if (isset($workshop->events[0]) && !is_null($workshop->events[0]->datumstart)) {
                $nextEventDate = $workshop->events[0]->datumstart->i18nFormat(Configure::read('DateFormat.de.DateLong2'));
            }

            $preparedWorkshops[] = [
                'id' => $workshop->uid,
                'name' => html_entity_decode((string) $workshop->name),
                'city' => $workshop->city,
                'postalCode' => $workshop->zip,
                'street' => html_entity_decode((string) $workshop->street),
                'street2' => html_entity_decode((string) $workshop->adresszusatz),
                'coordinates' => [
                    'lat' => $workshop->lat,
                    'lng' => $workshop->lng,
                ],
                'landingPage' => Configure::read('AppConfig.serverName') . Configure::read('AppConfig.htmlHelper')->urlWorkshopDetail($workshop->url),
                'logoUrl' => $workshop->image != '' ?  Configure::read('AppConfig.serverName') . Configure::read('AppConfig.htmlHelper')->getThumbs150Image($workshop->image, 'workshops') : Configure::read('AppConfig.serverName') . Configure::read('AppConfig.htmlHelper')->getThumbs100Image('rclogo-100.jpg', 'workshops'),
                'category' => $preparedCategories,
                'nextEvent' => $nextEventDate,
            ];

        }

        $this->set([
            'workshops' => $preparedWorkshops,
        ]);
        $this->viewBuilder()->setOption('serialize', ['workshops']);
        return null;
    }

    public function getWorkshopsForHyperModeWebsite(): void
    {

        /** @var WorkshopsTable $workshopsTable */
        $workshopsTable = $this->getTableLocator()->get('Workshops');
        $workshops = $workshopsTable->find('all',
        conditions: [
            'Workshops.status' => APP_ON,
        ],
        contain: [
            'Categories' => [
                'sort' => [
                    'Categories.name' => 'ASC', //3D-Reparatur should be first
                ],
            ],
            'Countries',
        ],
        order: ['Workshops.created' => 'DESC']);

        $preparedWorkshops = [];
        foreach($workshops as $workshop) {
            $preparedWorkshops[] = [
                'name' => $workshop->name,
                'city' => $workshop->city,
                'url' => Configure::read('AppConfig.serverName') . Configure::read('AppConfig.htmlHelper')->urlWorkshopDetail($workshop->url),
                'image' => $workshop->image != '' ?  Configure::read('AppConfig.serverName') . Configure::read('AppConfig.htmlHelper')->getThumbs150Image($workshop->image, 'workshops') : Configure::read('AppConfig.serverName') . Configure::read('AppConfig.htmlHelper')->getThumbs100Image('rclogo-100.jpg', 'workshops'),
                'hasOwnLogo' => $workshop->image == '' ? false : true,
                'categories' => \Cake\Utility\Hash::extract($workshop->categories, '{n}.name'),
            ];
        }

        $this->set([
            'workshops' => $preparedWorkshops,
        ]);
        $this->viewBuilder()->setOption('serialize', ['workshops']);

    }

    public function getSplitter(): void
    {
        $dir = new \DirectoryIterator(WWW_ROOT . Configure::read('AppConfig.splitterPath'));
        $prefix = 'SPLiTTER';
        $result = [];
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                if (preg_match('`^'.$prefix.'(.*)\.pdf`', $fileinfo->getFilename())) {
                    $parts = explode('_', $fileinfo->getFilename());
                    $month = (int) substr($parts[1], 0, 2);
                    $year = str_replace($prefix, '', $parts[0]);
                    $name = $prefix . ' No. ' . $month . '/' . $year;
                    $result[] = [
                        'url' => Configure::read('AppConfig.serverName') . Configure::read('AppConfig.splitterPath') . '/' . $fileinfo->getFilename(),
                        'name' => $name,
                        'size' => Configure::read('AppConfig.numberHelper')->toReadableSize($fileinfo->getSize()),
                    ];
                }
            }
        }

        $this->set([
            'splitter' => $result,
        ]);

        $this->viewBuilder()->setOption('serialize', ['splitter']);

    }

}
?>
