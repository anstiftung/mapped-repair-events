<?php
use Cake\Core\Configure;

$this->set('channelData', [
    'title' => Configure::read('AppConfig.titleSuffix'),
    'link' => $this->Url->build('/', true),
    'description' => 'Neues von den Initiativen auf ' . Configure::read('AppConfig.titleSuffix'),
    'language' => 'de-DE'
]);

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
    echo  $this->Rss->item([], [
        'title' => $post->name,
        'link' => $link,
        'guid' => ['url' => $link, 'isPermaLink' => 'true'],
        'description' => $body,
        'pubDate' => $post->publish->i18nFormat(Configure::read('DateFormat.de.DateLong2'))
    ]);
}

?>
