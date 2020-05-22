<?php

echo $this->element('highlightNavi' ,['main' => $post->blog->name]);
echo $this->element('heading', ['first' => $post->name]);

$this->element('addScript', ['script' =>
    JS_NAMESPACE.".AppFeatherlight.initLightboxImageGallery('.left .image-wrapper');
"]);

if ($this->request->getSession()->read('isMobile')) {
    $this->element('addScript', ['script' =>
        JS_NAMESPACE.".MobileFrontend.adaptPostDetail();
    "]);
};

?>

<div class="dotted-line-full-width"></div>

<div class="post-detail <?php echo $post->blog->url; ?>">

    <div class="left">
        <?php
            foreach($post->photos as $photo) {
                echo '<div class="image-wrapper">';
                    echo '<a href="'.$this->Html->getThumbs800ImageMultiple($photo->name).'">';
                        echo '<img alt="'.$photo->text.'" src="'.$this->Html->getThumbs280ImageMultiple($photo->name).'" / width="280" >';
                    echo '</a>';
                    if ($photo->text != '') {
                        echo '<div class="image-text">'.$photo->text.'</div>';
                    }
                echo '</div>';
            }
        ?>
    </div>

    <div class="right">
        <?php
            echo $this->element('post',
                ['post' => $post,
                    'type' => 'detail',
                    'blog' => $post->blog
                ]
            );
        ?>
    </div>

</div>
