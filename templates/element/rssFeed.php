<?php
/**
 * use BuildRssFeedComponent for this element
 */
?>
  <atom:link href="http://<?php echo $_SERVER['SERVER_NAME'].'/'.$this->request->getAttribute('here'); ?>" rel="self" type="application/rss+xml" />
  <title><?php echo $rssFeedData['title']; ?></title>
  <description><?php echo $rssFeedData['description']; ?></description>
  <language>de</language>
  <link><?php echo $rssFeedData['link']; ?></link>
  <lastBuildDate><?php echo $rssFeedData['lastBuildDate']; ?></lastBuildDate>

  <?php foreach($rssFeedData['items'] as $item) { ?>

    <item>

      <title><?php echo $item['title']; ?></title>
      <description><?php echo $item['description']; ?></description>
      <link><?php echo $item['link']; ?></link>
      <?php if (isset($item['pubDate'])) { ?>
          <pubDate><?php echo $item['pubDate']; ?></pubDate>
      <?php } ?>
      <guid><?php echo $item['link']; ?></guid> <?php /* TODO anschauen was guid macht, momentan ist es einfach der link */ ?>

      <?php
        if (!empty($item['enclosures'])) {
          foreach($item['enclosures'] as $enclosure) {
            $url = $enclosure['url'];
            if (!preg_match('/http:\/\//', $url)) {
                $url = 'http://'.$_SERVER['SERVER_NAME'] . $url;
            }
      ?>
        <media:content url="<?php echo $url; ?>" medium="<?php echo $enclosure['medium']; ?>">
          <?php if ($enclosure['alt'] != '') { ?>
            <media:title type="html"><?php echo htmlspecialchars($enclosure['alt']); ?></media:title>
          <?php } ?>
        </media:content>
      <?php
        }
       }
      ?>

    </item>

  <?php } ?>
