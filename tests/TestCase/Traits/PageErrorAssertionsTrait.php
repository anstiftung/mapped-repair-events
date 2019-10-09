<?php

namespace App\Test\TestCase\Traits;

trait PageErrorAssertionsTrait
{
    
    private function doAssertPagesForErrors()
    {
        
        $notRegexp = [
            'Error:'
        ];
        $this->assertResponseNotRegExp('/' . join('|', $notRegexp) . '/');
        
        $regexp = [
            '<\/body>',
        ];
        $this->assertResponseRegExp('/' . join('|', $regexp) . '/');
        
    }

}
?>