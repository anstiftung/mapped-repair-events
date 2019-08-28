<?php 
if (!isset($documentData)) {
    $documentData = [];
}
if (!isset($channelData)) {
    $channelData = [];
}
if (!isset($channelData['title'])) {
    $channelData['title'] = $this->fetch('title');
}
$channel = $this->Rss->channel([], $channelData, $this->fetch('content'));
echo $this->Rss->document($documentData, $channel);
?>