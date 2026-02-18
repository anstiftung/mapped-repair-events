<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Model\Entity\ApiToken;
use App\Test\TestCase\AppTestCase;
use App\Test\TestCase\Traits\LoginTrait;

class ApiTokensControllerTest extends AppTestCase
{
    use LoginTrait;

    public function testEditFormWithFixtureRecord1(): void
    {
        $this->loginAsAdmin();

        $this->post('/admin/apiTokens/edit/1', [
            'referer' => '/admin/apiTokens',
            'name' => 'Updated Test Token',
            'type' => ApiToken::TYPE_WORKSHOPS,
            'allowed_search_terms' => "Berlin\nHamburg",
            'allowed_domains' => "localhost\nexample.org",
            'status' => 1,
        ]);

        $this->assertResponseCode(302);

        $apiTokensTable = $this->getTableLocator()->get('ApiTokens');
        $apiToken = $apiTokensTable->get(1);

        $this->assertSame('Updated Test Token', $apiToken->name);
        $this->assertSame(ApiToken::TYPE_WORKSHOPS, $apiToken->type);
        $this->assertSame('["Berlin","Hamburg"]', $apiToken->allowed_search_terms);
        $this->assertSame('["localhost","example.org"]', $apiToken->allowed_domains);
        $this->assertTrue((bool)$apiToken->status);
    }

    public function testEditFormShowsValidationErrorWhenWorkshopSearchTermsAreEmpty(): void
    {
        $this->loginAsAdmin();

        $apiTokensTable = $this->getTableLocator()->get('ApiTokens');
        $apiTokenBefore = $apiTokensTable->get(1);

        $this->post('/admin/apiTokens/edit/1', [
            'referer' => '/admin/apiTokens',
            'name' => 'Updated Test Token',
            'type' => ApiToken::TYPE_WORKSHOPS,
            'allowed_search_terms' => '',
            'allowed_domains' => "localhost\nexample.org",
            'status' => 1,
        ]);

        $this->assertNoRedirect();
        $this->assertResponseContains('Für den Typ Workshops API muss mindestens ein erlaubter Suchbegriff angegeben werden.');

        $apiTokenAfter = $apiTokensTable->get(1);
        $this->assertSame($apiTokenBefore->name, $apiTokenAfter->name);
        $this->assertSame($apiTokenBefore->allowed_search_terms, $apiTokenAfter->allowed_search_terms);
    }

    public function testEditFormShowsValidationErrorWhenNonWorkshopContainsSearchTerms(): void
    {
        $this->loginAsAdmin();

        $apiTokensTable = $this->getTableLocator()->get('ApiTokens');
        $apiTokenBefore = $apiTokensTable->get(5);

        $this->post('/admin/apiTokens/edit/5', [
            'referer' => '/admin/apiTokens',
            'name' => 'Updated Splitter Token',
            'type' => ApiToken::TYPE_SPLITTER,
            'allowed_search_terms' => "Berlin\nHamburg",
            'allowed_domains' => "localhost\nexample.org",
            'status' => 1,
        ]);

        $this->assertNoRedirect();
        $this->assertResponseContains('Erlaubte Suchbegriffe sind nur für den Typ Workshops API erlaubt und müssen für alle anderen Typen leer sein.');

        $apiTokenAfter = $apiTokensTable->get(5);
        $this->assertSame($apiTokenBefore->name, $apiTokenAfter->name);
        $this->assertSame($apiTokenBefore->allowed_search_terms, $apiTokenAfter->allowed_search_terms);
    }
}
