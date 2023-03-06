<?php
namespace App\Log\Engine;

use Cake\Mailer\Mailer;
use Cake\Core\Configure;
use Cake\Log\Engine\FileLog;
use Cake\Network\Exception\SocketException;
use Cake\Routing\Router;
use Cake\Utility\Text;

class FileAndEmailLog extends FileLog
{

    public function log($level, $message, array $context = []): void
    {
        parent::log($level, $message, $context);
        if (Configure::read('emailErrorLoggingEnabled')) {
            $this->sendEmailWithErrorInformation($message);
        }
    }

    private function sendEmailWithErrorInformation($message)
    {

        $ignoredExceptionsRegex = [
           '(MissingController|MissingAction|RecordNotFound|NotFound|InvalidCsrfToken)Exception',
           'Workshops\/rss\/home\.ctp',
           'RssHelper is deprecated',
           'UsersController::publicProfile()',
           'workshops\/ajaxGetAllWorkshopsForMap',
            preg_quote('{"results":[{"address_components"'),
            'Form tampering protection token validation failed',
        ];
        if (preg_match('`' . join('|', $ignoredExceptionsRegex) . '`', $message)) {
            return false;
        }

        $request = Router::getRequest();
        $loggedUser = [];
        if ($request) {
            $session = $request->getSession();
            if ($session->read('Auth.User.uid') !== null) {
                $loggedUser = $session->read('Auth');
            }
        }
        $preparedHostWithoutProtocol = Configure::read('AppConfig.htmlHelper')->getHostWithoutProtocol(Configure::read('AppConfig.serverName'));
        $preparedHostWithoutProtocol = str_replace('www.', '', $preparedHostWithoutProtocol);
        $subject = 'ErrorLog ' . $preparedHostWithoutProtocol . ': ' . Text::truncate($message, 150) . ' ' . date('Y-m-d H:i:s');
        try {
            $email = new Mailer('default');
            $email->viewBuilder()->setTemplate('debug');
            $email->setTo(Configure::read('AppConfig.debugMailAddress'))
            ->setEmailFormat('html')
            ->setSubject($subject)
            ->setViewVars(array(
                'message' => $message,
                'loggedUser' => $loggedUser
            ))
            ->send();
        } catch (SocketException $e) {
            return false;
        }

    }

}
