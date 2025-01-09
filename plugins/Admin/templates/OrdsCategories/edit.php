<?php
declare(strict_types=1);
use Cake\Core\Configure;

$this->element('addScript', ['script' =>
    JS_NAMESPACE.".Helper.bindCancelButton();".
    JS_NAMESPACE.".Helper.layoutEditButtons();
"]);
?>

<div class="admin edit">

    <?php
    echo $this->Form->create($ordsCategory, [
        'novalidate' => 'novalidate'
    ]);
    ?>
    <div class="edit">
        <?php
            echo $this->element('heading', ['first' => $metaTags['title']]);
            echo $this->Form->hidden('referer', ['value' => $referer]);
            $this->Form->unlockField('referer');
            echo $this->Form->control('OrdsCategories.name', ['label' => 'Name']).'<br />';
            echo $this->Form->control('OrdsCategories.status', ['type' => 'select', 'options' => Configure::read('AppConfig.status')]).'<br />';
        ?>
    </div>

    <?php
        echo $this->element('cancelAndSaveButton', ['saveLabel' => 'Speichern']);
        echo $this->Form->end();
    ?>

</div>
