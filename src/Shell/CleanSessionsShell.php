<?php

namespace App\Shell;

class CleanSessionsShell extends AppShell
{

    public function main()
    {
        exec('find ' . TMP . DS . 'sessions -type f -cmin +14400 -delete');
    }

}
