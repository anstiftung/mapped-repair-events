<?php
namespace App\Log\Engine;

use Cake\Mailer\Email;
use App\Network\AppSession;
use Cake\Core\Configure;
use Cake\Log\Engine\FileLog;
use Cake\Network\Exception\SocketException;
use Cake\Utility\Text;

class FileAndEmailLog extends FileLog
{

    public function log($level, $message, array $context = [])
    {
        
        $result = parent::log($level, $message, $context);
        
        if (Configure::read('emailErrorLoggingEnabled')) {
            $this->sendEmailWithErrorInformation($message);
        }
        
        return $result;
    }
    
    private function sendEmailWithErrorInformation($message)
    {
        
        $ignoredExceptionsRegex = '/(MissingController|MissingAction|RecordNotFound|NotFound)Exception|(Workshops\/rss\/home\.ctp)|(RssHelper is deprecated)|(UsersController::publicProfile())/';
        if (preg_match($ignoredExceptionsRegex, $message)) {
            return false;
        }
        
        if (preg_match(preg_quote('`{"results":[{"address_components"`'), $message)) {
            return false;
        }
        
        $session = new AppSession();
        $loggedUser = [];
        if ($session->read('Auth.User.uid') !== null) {
            $loggedUser = $session->read('Auth');
        }
        
        $subject = 'ErrorLog RepIni: ' . Text::truncate($message, 90) . ' ' . date('Y-m-d H:i:s');
        try {
            $email = new Email('default');
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
