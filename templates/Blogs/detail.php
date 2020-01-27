<?php
echo $this->element('highlightNavi',['main' => $blog->name]);
?>

<div class="blog-detail <?php echo $blog->url; ?>">

<?php

    $blogHeading = $blog->name;
    if ($blogHeading == 'Aktuelles') {
        $blogHeading = 'Aktuelles & Neuigkeiten';
    }
    echo '<br>' . $this->element('heading', [
        'first' => $blogHeading
    ]);
	
    if (count($posts) > 0) {
      echo '<span class="rsslink-wrapper">
                <a class="rsslink" target="_blank" href="'.$this->Html->urlFeed($blog->url).'" title="'.__('get platform news via E-Mail').'">
                    <i class="fas fa-rss"></i>
                </a>
        </span>';
    }

    if ($blog->text != '') {
        echo '<div class="text-wrapper">';
            echo $blog->text;
        echo '</div>';
    }
    
    foreach($posts as $post) { 
    
        $params = [];
        $params['post'] = $post;
        $params['type'] = 'preview';
        $params['blog'] = $post->blog;
        
        if (!$this->request->getSession()->read('isMobile') && $post->image != '') {
            $postImage = $this->Html->getThumbs100Image($post->image, 'posts');
            $params['imageSize'] = 150;
            $imageInfo = getimagesize(substr(WWW_ROOT.$postImage, 0, -11));
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            if ($width < $height) {
                // Portrait or Square
                $params['postLength'] = 860;
            }
        }
        echo '<br /><br />'.$this->element('post', $params);
        
    }
?>

</div>

<?php
$this->element('addScript', ['script' => "
    $(document).on('mouseover', ' .rsslink', function(e){
        if(!$(this).data('rsslink')){
            $(this).tooltip({
                content: function() {
                    return $(this).attr('title');
                } 
            }).triggerHandler('mouseover');
        }
    });
"]);
?> 