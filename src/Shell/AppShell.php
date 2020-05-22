<?php
/**
 * Add your application-wide methods in the class below, your shells
 * will inherit them.
 */
namespace App\Shell;

use Cake\Console\Shell;

class AppShell extends Shell {

    public function getEnvironment() {
        $environment = 'live';
        if (isset($_SERVER['HOMEPATH']) &&  $_SERVER['HOMEPATH'] == '\Users\mario') {
            $environment = 'dev';
        }
        return $environment;
    }

}
