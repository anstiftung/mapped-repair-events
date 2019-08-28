<?php
/**
 * damit der controller in jeder componente verfügbar ist
 * @author Mario Rothauer <marothauer@gmail.com>
 */
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;

class AppComponent extends Component
{

    protected $controller;

    protected $session;

    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        parent::__construct($registry, $config);
        $this->controller = $this->_registry->getController();
        if ($this->controller) {
            $this->session = $this->controller->request->getSession();
        }
    }
}

?>