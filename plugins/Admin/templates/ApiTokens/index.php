<?php
declare(strict_types=1);

use App\Model\Entity\ApiToken;

echo $this->element('list', [
    'objects' => $objects,
    'heading' => 'API Token',
    'editMethod' => ['url' => 'urlApiTokenEdit'],
    'newMethod' => ['url' => 'urlApiTokenAdd'],
    'selectable' => false,
    'fields' => [
        ['name' => 'id', 'label' => 'ID'],
        ['name' => 'name', 'label' => 'Name'],
        ['name' => 'type', 'label' => 'Typ', 'values' => (object)ApiToken::TYPES],
        ['name' => 'allowed_search_terms', 'label' => 'Erlaubte Suchbegriffe'],
        ['name' => 'allowed_domains', 'label' => 'Erlaubte Domains'],
        ['name' => 'last_used', 'type' => 'datetime', 'label' => 'Zuletzt verwendet'],
        ['name' => 'expires_at', 'type' => 'datetime', 'label' => 'Ablaufdatum'],
        ['name' => 'created', 'type' => 'datetime', 'label' => 'Erstellt'],
    ],
]);
?>
