<?php
use Cake\Core\Configure;

$items = [];
foreach ($posts as $post) {
    $link = $this->Html->urlPostDetail($post->url);

    $body = $post->publish->i18nFormat(Configure::read('DateFormat.de.DateLong2'));
    $body .= ' // ' . $post->name;
    $body .= '<br />' . h(strip_tags($post->text));

    $body = $this->Text->truncate($body, 400, [
        'ending' => '...',
        'exact'  => true,
        'html'   => true,
    ]);

    $preparedItem = [
        'title' => $post->name,
        'link' => $link,
        'guid' => ['url' => $link],
        'description' => $body,
        'pubDate' => $post->publish->i18nFormat(Configure::read('DateFormat.de.DateLong2'))
    ];

    if (!empty($post->photos)) {
        $imageUrl = $this->Html->getThumbs800ImageMultiple($post->photos[0]->name);
        $length = filesize(WWW_ROOT . $imageUrl);
        $mimeType = mime_content_type(WWW_ROOT . $imageUrl);
        $preparedItem['enclosure'] = ['url' => $imageUrl, 'length' => $length, 'type' => $mimeType];
    }

    $items[] = $preparedItem;
}

$this->set('channelData', [
    'title' => Configure::read('AppConfig.titleSuffix'),
    'link' => $this->Url->build('/', ['fullBase' => true]),
    'description' => 'Neues von den Initiativen auf ' . Configure::read('AppConfig.titleSuffix'),
    'language' => 'de-DE',
    'items' => $items
]);

?>
