<?php
declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Http\Session;

class AppComponent extends Component
{

    protected Controller $controller;
    protected Session $session;

    /**
     * @param \Cake\Controller\ComponentRegistry<\Cake\Controller\Controller> $registry
     * @param array<string, mixed> $config
     */
    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        parent::__construct($registry, $config);
        $this->controller = $this->_registry->getController();
        $this->session = $this->controller->getRequest()->getSession();
    }
}

?>