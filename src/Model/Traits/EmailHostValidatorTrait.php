<?php
namespace App\Model\Traits;

use Cake\Validation\Validator;

trait EmailHostValidatorTrait {
    
    public function addValidEmailHost(Validator $validator)
    {
        $validator->add('email', 'isValidHost', [
            'rule' => 'validateEmailHostExisting',
            'provider' => 'table',
            'message' => 'Der Server-Name ist nicht gültig.'
        ]);
        return $validator;
    }
    
    public function validateEmailHostExisting($email) {
        if (!function_exists('curl_version')) {
            return true;
        }
        $url = explode('@', $email)[1];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, true);    // we want headers
        curl_setopt($ch, CURLOPT_NOBODY, true);    // we don't need body
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_TIMEOUT,10);
        curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode > 0 && $httpcode < 400) {
            return true;
        }
        return false;
    }
    
}

?>