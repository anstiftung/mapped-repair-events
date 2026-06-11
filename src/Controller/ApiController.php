<?php
declare(strict_types=1);
namespace App\Controller;

use App\Controller\Traits\Api\ApiGetSplitterTrait;
use App\Controller\Traits\Api\ApiGetStatisticsTrait;
use App\Controller\Traits\Api\ApiGetWorkshopsTrait;
use Cake\Event\EventInterface;
use Cake\View\JsonView;

class ApiController extends AppController
{

    use ApiGetSplitterTrait;
    use ApiGetStatisticsTrait;
    use ApiGetWorkshopsTrait;

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

}
?>
