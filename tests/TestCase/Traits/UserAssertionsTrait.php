<?php
declare(strict_types=1);

namespace App\Test\TestCase\Traits;

trait UserAssertionsTrait
{

    private function doUserPrivacyAssertions(): void
    {
        $this->assertResponseNotContains('<span class="public-name-wrapper">John Doe</span>');
        $this->assertResponseNotContains('<span class="public-name-wrapper">John</span>');
        $this->assertResponseNotContains('<span class="public-name-wrapper">Doe</span>');
    }

}
?>