<?php
declare(strict_types=1);

namespace App\Test\TestCase\Traits;

trait HtmlOutputAssertionsTrait
{

    private function doAssertHtmlOutput()
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