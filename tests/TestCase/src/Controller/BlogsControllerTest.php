<?php

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\AppTestCase;
use App\Test\TestCase\Traits\LogFileAssertionsTrait;
use Cake\TestSuite\IntegrationTestTrait;

class BlogsControllerTest extends AppTestCase
{

    use IntegrationTestTrait;
    use LogFileAssertionsTrait;

    public function testFeed() {
        $this->get('/feed.rss');
        $this->assertResponseOk();
        $this->assertResponseContains('<?xml version="1.0" encoding="UTF-8"?>');
        $this->assertResponseContains('<rss version="2.0">');
        $this->assertResponseContains('<title>Test Post</title>');
        $this->assertResponseContains('<language>de-DE</language>');
        $this->assertResponseContains('<pubDate>Wed, 09 Oct 2019 00:00:00 +0200</pubDate>');
        $this->assertResponseContains('</rss>');
    }

}
?>