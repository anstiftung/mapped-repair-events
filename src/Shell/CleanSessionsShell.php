<?php

namespace App\Shell;

class CleanSessionsShell extends AppShell
{

    public function main()
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

    }

}
