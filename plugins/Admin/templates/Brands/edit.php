<?php
use Cake\Core\Configure;

$this->element('addScript', array('script' =>
    JS_NAMESPACE.".Helper.bindCancelButton();".
    JS_NAMESPACE.".Helper.layoutEditButtons();
"));
?>

<div class="admin edit">

    <?php
    echo $this->Form->create($brand, [
        'novalidate' => 'novalidate'
    ]);
    ?>
    <div class="edit">
        <?php
            echo $this->element('heading', ['first' => $metaTags['title']]);
            echo $this->Form->hidden('referer', ['value' => $referer]);
            $this->Form->unlockField('referer');
            echo $this->Form->control('Brands.name', ['label' => 'Name']).'<br />';
            echo $this->Form->control('Brands.status', ['type' => 'select', 'options' => Configure::read('AppConfig.approvalStatus')]).'<br />';
        ?>
    </div>

    <?php
        echo $this->element('cancelAndSaveButton', ['saveLabel' => 'Speichern']);
        echo $this->Form->end();
    ?>

</div>
