<?php
use Cake\Core\Configure;
?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<url>
<loc><?php echo Configure::read('AppConfig.serverName'); ?></loc>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>

<?php foreach ($pages as $page) { ?>
    <url>
    <loc><?php echo Configure::read('AppConfig.serverName').$this->MyHtml->urlPageDetail($page->url); ?></loc>
    <lastmod><?php echo $this->Time->toAtom($page->updated); ?></lastmod>
    <priority>0.8</priority>
    </url>
<?php } ?>

<?php foreach ($posts as $post) { ?>
    <url>
    <loc><?php echo Configure::read('AppConfig.serverName').$this->MyHtml->urlPostDetail($post->url); ?></loc>
    <lastmod><?php echo $this->Time->toAtom($post->updated); ?></lastmod>
    <priority>0.8</priority>
    </url>
<?php } ?>

<?php foreach ($workshops as $workshop) { ?>
    <url>
    <loc><?php echo Configure::read('AppConfig.serverName').$this->MyHtml->urlWorkshopDetail($workshop->url); ?></loc>
    <lastmod><?php echo $this->Time->toAtom($workshop->updated); ?></lastmod>
    <priority>0.8</priority>
    </url>
<?php } ?>

</urlset>