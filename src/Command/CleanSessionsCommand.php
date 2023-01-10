<?php

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;

class CleanSessionsCommand extends Command
{

    public function execute(Arguments $args, ConsoleIo $io)
    {

        $path = TMP . 'sessions' . DS;

        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                $realFile = $path . $file;
                if (is_file($realFile)) {
                    $filelastmodified = filemtime($realFile);
                    if((time() - $filelastmodified) > 24*60*60)
                    {
                        unlink($realFile);
                    }
                }
            }
            closedir($handle);
        }

        return static::CODE_SUCCESS;

    }

}
