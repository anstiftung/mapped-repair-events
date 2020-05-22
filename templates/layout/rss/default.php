<?php
use Feed\View\RssView;

if (!isset($channelData)) {
    $channelData = [];
}
if (!isset($channelData['title'])) {
    $channelData['title'] = $this->fetch('title');
}

$viewVars = ['channel' => $channelData, '_serialize' => 'channel'];
$View = new RssView($this->request, $this->response, null, ['viewVars' => $viewVars]);
echo $View->render(false);

?>