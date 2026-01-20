<?php
declare(strict_types=1);
namespace App\Log\Engine;

use Cake\Mailer\Mailer;
use Cake\Core\Configure;
use Cake\Log\Engine\FileLog;
use Cake\Network\Exception\SocketException;
use Cake\Routing\Router;
use Cake\Utility\Text;
use Stringable;

class FileAndEmailLog extends FileLog
{

    /**
     * @param array<string, string> $context
     */
    public function log($level, Stringable|string $message, array $context = []): void
    {
        parent::log($level, $message, $context);
        if (Configure::read('emailErrorLoggingEnabled')) {
            $this->sendEmailWithErrorInformation($message);
        }
    }

    private function sendEmailWithErrorInformation(string $message): bool
    {

        $ignoredExceptionsRegex = [
           '(MissingController|MissingAction|MissingTemplate|RecordNotFound|NotFound|InvalidCsrfToken|Forbidden|Unauthenticated|MissingRoute|MissingHelper)Exception',
            preg_quote('Workshops/rss/home.ctp', '/'),
           'RssHelper is deprecated',
           'UsersController::publicProfile()',
            preg_quote('workshops/ajaxGetAllWorkshopsForMap', '/'),
            preg_quote('{"results":[{"address_components"', '/'),
            'Form tampering protection token validation failed',
            preg_quote('`FormProtector` instance has not been created.', '/'),
            'Recipient address rejected',
            'Domain does not exist',
            'categories must only contain integers',
        ];
        if (preg_match('/' . join('|', $ignoredExceptionsRegex) . '/', $message)) {
            return false;
        }

        $identity = null;
        $request = Router::getRequest();
        if ($request !== null) {
            $identity = $request->getAttribute('identity');
        }

        $preparedHostWithoutProtocol = Configure::read('AppConfig.htmlHelper')->getHostWithoutProtocol(Configure::read('AppConfig.serverName'));
        $preparedHostWithoutProtocol = str_replace('www.', '', $preparedHostWithoutProtocol);
        $subject = 'ErrorLog ' . $preparedHostWithoutProtocol . ': ' . Text::truncate($message, 150) . ' ' . date('Y-m-d H:i:s');
        try {
            $email = new Mailer();
            $email->viewBuilder()->setTemplate('debug');
            $email->setTo(Configure::read('AppConfig.debugMailAddress'))
            ->setSubject($subject)
            ->setViewVars([
                'message' => $message,
                'identity' => $identity,
            ])
            ->send();
        } catch (SocketException) {
            return false;
        }

        return true;

    }

}
