<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\Fixture\ApiTokensFixture;
use App\Test\TestCase\AppTestCase;
use Cake\Core\Configure;

class ApiControllerSplitterTest extends AppTestCase
{
    public function testGetSplitterOk(): void
    {
        Configure::write('AppConfig.splitterPath', '/files');
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . ApiTokensFixture::VALID_SPLITTER_TOKEN,
                'Origin' => 'http://localhost',
            ],
        ]);
        $this->get('/api/splitter');
        $this->assertResponseOk();
    }
}
