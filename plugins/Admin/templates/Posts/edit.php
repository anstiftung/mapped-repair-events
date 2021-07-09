<?php
use Cake\Core\Configure;

    $this->element('addScript', array('script' =>
        JS_NAMESPACE.".Helper.doCurrentlyUpdatedActions(".$isCurrentlyUpdated.");".
        JS_NAMESPACE.".Helper.bindCancelButton(".$uid.");".
        JS_NAMESPACE.".Helper.layoutEditButtons();
    "));
    echo $this->element('highlightNavi', ['main' => 'Posts']);
    echo $this->element('datepicker');
?>

<div class="admin edit">

        <div class="edit">

        <?php echo $this->element('heading', ['first' => 'Post bearbeiten']); ?>

        <?php
            echo $this->Form->create(
                $post,
                [
                    'novalidate' => 'novalidate',
                    'id' => 'EventEdit'
                ]
            );
            echo $this->Form->hidden('referer', ['value' => $referer]);
            $this->Form->unlockField('referer');

            echo $this->Form->control('Posts.name', ['label' => 'Titel']).'<br />';

            echo $this->element('urlEditField', [
                'type' => 'Posts',
                'urlPrefix' => '/post/',
                'type_de' => 'der Post',
                'data' => $post
            ]).'<br />';

            echo $this->element('upload/single', [
                 'field' => 'Posts.image'
                ,'objectType'   => 'posts'
                ,'image' => $post->image
                ,'uid' => $uid
                ,'label' => 'Vorschaubild'
            ]).'<br />';
            echo $this->Form->control('Posts.image_alt_text', ['label' => 'Alt-Text für Bild']).'<br />';

            echo $this->element('upload/multiple', array(
                'field' => 'Posts.image',
                'objectType' => 'posts',
                'images' => $photos,
                'uid' => $uid,
                'label' => 'Bilder'
            )).'<br />';

            echo $this->Form->control('Posts.city', ['label' => 'Ort']).'<br />';
            echo $this->Form->control('Posts.blog_id', ['type' => 'select', 'options' => $blogs, 'label' => 'Blog']).'<br />';
            echo $this->Form->control('Posts.author', ['label' => 'Autor']).'<br />';
            echo $this->Form->control('Posts.publish', ['class' => 'datepicker-input', 'label' => 'Veröffentlicht ab', 'type' => 'text', 'format' => Configure::read('DateFormat.de.DateLong2'), 'value' => !empty($post->publish) ? $post->publish->i18nFormat(Configure::read('DateFormat.de.DateLong2')) : '']).'<br />';

            echo $this->Form->control('Posts.status', ['type' => 'select', 'options' => Configure::read('AppConfig.status')]).'<br />';

            echo $this->element('metatagsFormfields', ['entity' => 'Posts']);

        ?>
    </div>

    <?php
        echo $this->element('cancelAndSaveButton');
    ?>
    <div class="ckeditor-edit">
      <?php
        echo $this->element('ckeditorEdit', [
           'value' => $post->text,
           'name' => 'Posts.text',
           'uid' => $uid,
           'objectType' => 'posts'
         ]
       );
      ?>
    </div>

      <?php
        echo $this->Form->end();
      ?>


</div>

<div class="sc"></div> <?php /* wegen ckeditor */ ?>