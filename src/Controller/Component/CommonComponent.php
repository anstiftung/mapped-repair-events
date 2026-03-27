<?php
declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Event\EventInterface;

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
     */
    /**
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event
     */
    public function startup(EventInterface $event): void
    {

        $request = $this->controller->getRequest();

        $params = ['pass', 'id', 'uid', 'city'];

        if ($this->controller->getRequest()->getData()) {
            $newData = $this->trimAndSanitizeDeep($request->getData());
            foreach ($newData as $k => $v) {
                $k = (string) $k;
                if ($request->getData($k) !== $v) {
                    $request = $request->withData($k, $v);
                }
            }
        }
        if ($request->getQuery()) {
            $queryData = $this->trimAndSanitizeDeep($request->getQuery());
            if ($queryData !== $request->getQuery()) {
                $request = $request->withQueryParams($queryData);
            }
        }
        foreach($params as $param) {
            if ($request->getParam($param)) {
                $paramData = $this->trimAndSanitizeDeep($request->getParam($param));
                if ($paramData !== $request->getParam($param)) {
                    $request = $request->withParam($param, $paramData);
                }
            }
        }

        if ($request !== $this->controller->getRequest()) {
            $this->controller->setRequest($request);
        }

    }

    /**
     * Trim and sanitize recursively
     */
    private function trimAndSanitizeDeep(mixed $value, bool $transformNullToString = false, ?string $key = null): mixed
    {

        // Laminas\Diactoros\UploadedFile
        if (is_object($value)) {
            return $value;
        }

        if ($this->isUntouchedPasswordField($key)) {
            return $value;
        }

        if (is_array($value)) {
            foreach ($value as $childKey => $childValue) {
                $value[$childKey] = $this->trimAndSanitizeDeep(
                    $childValue,
                    $transformNullToString,
                    is_string($childKey) ? $childKey : null,
                );
            }
            return $value;
        }

        $config = \HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', TMP . 'cache' . DS . 'html_purifier');
        $config->set('HTML.SafeIframe', true);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('Attr.EnableID', true); // enables anchors: <a name="xxx">Text</a>
        if (!is_null($this->controller->getRequest()->getAttribute('identity'))) {
            if ($this->controller->getRequest()->getAttribute('identity')->getOriginalData()->isAdmin()) {
                $config->set('URI.SafeIframeRegexp', '%(.*)%');
            }
        } else {
            $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%');
        }
        $purifier = new \HTMLPurifier($config);

        return ($value === null && !$transformNullToString) ? $value : trim($purifier->purify($value));
    }

    private function isUntouchedPasswordField(?string $key): bool
    {
        if ($key === null) {
            return false;
        }

        return str_contains(strtolower($key), 'password');
    }

}