<?php
use Cake\Core\Configure;

$this->set('channelData', [
    'title' => Configure::read('AppConfig.titleSuffix') . ' Reparaturtermine',
    'link' => $this->Url->build('/', true),
    'description' => 'RSS-Feed vom ' . Configure::read('AppConfig.titleSuffix'),
    'language' => 'de-DE'
]);

foreach ($events as $event) {
    
    $link = $this->Html->urlEventDetail($event->workshop->url, $event->uid, $event->datumstart);
    
    $body = $event->datumstart->i18nFormat(Configure::read('DateFormat.de.DateLong2'));
    
    $uhrzeitstart = $event->uhrzeitstart->i18nFormat(Configure::read('DateFormat.de.TimeShort'));
    if ($uhrzeitstart != '00:00') {
        $body .= ' um ' . $uhrzeitstart;
    }
    $uhrzeitend = $event->uhrzeitend->i18nFormat(Configure::read('DateFormat.de.TimeShort'));
    if ($uhrzeitstart != '00:00') {
        $body .= ' und endet um ' . $uhrzeitend;
    }
    $body .= ' // ' . $event->workshop->name.' // ' . ' ' . $event->ort;
    if ($event->veranstaltungsort != '') {
        $body .= ' // ' . $event->veranstaltungsort;
    }
    $body .= '<br />'.h(strip_tags($event->eventbeschreibung));
    
    $body = $this->Text->truncate($body, 400, [
        'ending' => '...',
        'exact'  => true,
        'html'   => true,
    ]);
    echo  $this->Rss->item([], [
        'title' => $event->workshop->name,
        'link' => $link,
        'guid' => ['url' => $link, 'isPermaLink' => 'true'],
        'description' => $body,
        'pubDate' => $event->datumstart->i18nFormat(Configure::read('DateFormat.de.DateLong2'))
    ]);
}

?>
