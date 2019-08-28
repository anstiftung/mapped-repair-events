<?php

namespace App\Controller\Component;

use Cake\Event\Event;

/**
 * A component included in every app to take care of common stuff.
 *
 * @author Mark Scherer
 * @copyright 2012 Mark Scherer
 * @license MIT
 */
class CommonComponent extends AppComponent {

	/**
	 * For this helper the controller has to be passed as reference
	 * for manual startup with $disableStartup = true (requires this to be called prior to any other method)
	 *
	 * @param \Cake\Event\Event $event
	 * @return void
	 */
	public function startup(Event $event) {
		
		$request = $this->controller->getRequest();
		
		if ($this->controller->getRequest()->getData()) {
		    
		    $newData = $this->trimDeep($request->getData());
		    foreach ($newData as $k => $v) {
		        if ($request->getData($k) !== $v) {
		            $request = $request->withData($k, $v);
		        }
		    }
		}
		if ($request->getQuery()) {
		    $queryData = $this->trimDeep($request->getQuery());
		    if ($queryData !== $request->getQuery()) {
		        $request = $request->withQueryParams($queryData);
		    }
		}
		if ($request->getParam('pass')) {
		    $passData = $this->trimDeep($request->getParam('pass'));
		    if ($passData !== $request->getParam('pass')) {
		        $request = $request->withParam('pass', $passData);
		    }
		}
		
		if ($request === $this->controller->getRequest()) {
		    return;
		}
		
		$this->controller->setRequest($request);
		
	}
	
	/**
	 * Trim recursively
	 *
	 * @param string|array|null $value
	 * @param bool $transformNullToString
	 * @return array|string
	 */
	private function trimDeep($value, $transformNullToString = false) {
	    if (is_array($value)) {
	        foreach ($value as $k => $v) {
	            $value[$k] = $this->trimDeep($v, $transformNullToString);
	        }
	        return $value;
	    }
	    return ($value === null && !$transformNullToString) ? $value : trim($value);
	}

}