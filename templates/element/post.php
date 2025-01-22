<?php
declare(strict_types=1);

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;

if (!isset($postLength)) $postLength = 550;

if (!isset($imageSize)) $imageSize = 100;
$imageSizeFunction = 'getThumbs' . $imageSize . 'Image';

// auf übersicht bzw. detail-seite bild anzeigen (falls vorhanden)
$showImage = !empty($post->image) &&
    ($type == 'preview') || ($type == 'detail' && !in_array($blog->url, $this->Html->getPostTypesWithPreview())
);

?>

<div class="post-wrapper">

    <div class="text-wrapper">

<?php
if ($type != 'detail') {
    $heading = $this->Html->link(
             $post->name,
             $this->Html->urlPostDetail($post->url),
             ['escape' => false],
    );
    echo '<div class="postteaser"><h2>'.$heading.'</h2>';
}

echo '<div class="post-meta">';

    $output =  [];

    if (!in_array($blog->url, ['publikationen'])) {
        $output[] = $post->publish->i18nFormat(Configure::read('DateFormat.de.DateLong'));
    }

    if ($post->where != '') {
          $output[] = $post->where;
    }

    $output[] = '<a href="'.$this->Html->urlBlogDetail($blog->url).'">'.$blog->name.'</a>';

    if ($post->author != '') {
        $output[] = '<a href="'.$this->Html->urlUserProfile($post->owner).'">'.trim($post->author).'</a>';
    }

    echo join(' | ', $output);

echo '</div>'; // .post-meta

if ($type == 'detail') {
    echo '<div class="sc"></div>';
}

echo '<div class="image-text-wrapper">';

    if ($type == 'detail' || !in_array($blog->url, $this->Html->getPostTypesWithPreview())) {
      $postText = $post->text;
    } else {
      $postText = StringComponent::prepareTextPreview($post->text);
      $postText = strip_tags($post->text);
      $postText = StringComponent::makeNoFollow($postText);
      $postText = StringComponent::cutHtmlString($postText, $postLength);
      if (strlen($postText) < strlen($post->text)) {
        // weiterlesen-link vor dem letzten </p> eifügen
        if (strlen($post->text) >= $postLength) {
          $postText = substr($postText, 0, strlen($postText) - 4).' ...';
          $postText .=  $this->Html->link(__('News read on'),
              ['controller' => 'posts',
                      'action' => 'detail',
                    $post->url],
                    ['class' => 'button rounded', 'style' => 'margin:10px 10px 20px 0px;float:right;']
              );
        }
      }
    }

//detail
echo '<div class="editor-text">';

if ($showImage) {
      if ($type == 'preview') {
        echo '<a title="'.$post->name.'" href="'.$this->Html->urlPostDetail($post->url).'">';
      }
        echo '<img alt="'.$post->image_alt_text.'" title="'.$post->image_alt_text.'"
            src="'.$this->Html->$imageSizeFunction($post->image, 'posts').'" />';
      if ($type == 'preview') {
        echo '</a>';
      }
}

//detail

echo $postText;

if ($type == 'detail') {
    echo '<a class="button" style="margin:10px 10px 20px 0px;float:right;padding:0px 8px" href="'.$this->Html->urlBlogDetail($blog->url).'">'.__('Go to overview').'</a>';
} else {
    echo '</div>'; // .post-teaser
}

echo '<br /><br />';

echo '</div>'; // .editor-text
echo '</div>'; // .image-text-wrapper

echo '</div>'; // .text-wrapper
echo '</div>'; // .post-wrapper

?>
