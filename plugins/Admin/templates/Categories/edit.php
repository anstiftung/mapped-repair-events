<?php
use Cake\Core\Configure;

$this->element('addScript', array('script' =>
    JS_NAMESPACE.".Helper.bindCancelButton();".
    JS_NAMESPACE.".Helper.layoutEditButtons();
"));
?>

<div class="admin edit">

    <?php
    echo $this->Form->create($category, [
        'novalidate' => 'novalidate'
    ]);
    ?>
    <div class="edit">
        <?php
            echo $this->element('heading', ['first' => $metaTags['title']]);
            echo $this->Form->hidden('referer', ['value' => $referer]);
            echo $this->Form->control('Categories.name', ['label' => 'Name']).'<br />';
            echo $this->Form->control('Categories.visible_on_platform', ['type' => 'select', 'label' => 'Sichtbar', 'options' => Configure::read('AppConfig.yesno')]).'<br />';
            echo $this->Form->control('Categories.carbon_footprint', ['type' => 'text', 'label' => 'Carbon Footprint']).'<br />';
            echo $this->Form->control('Categories.material_footprint', ['type' => 'text', 'label' => 'Material Footprint']).'<br />';
            echo $this->Form->control('Categories.parent_id', ['style' => 'width: 295px;', 'type' => 'select', 'label' => 'Oberkategorie', 'options' => $mainCategories, 'empty' => 'Oberkategorie auswählen...']).'<br />';
            echo $this->Form->control('Categories.status', ['type' => 'select', 'options' => Configure::read('AppConfig.approvalStatus')]).'<br />';
        ?>
    </div>

    <?php
        echo $this->element('cancelAndSaveButton', ['saveLabel' => 'Speichern']);
        echo $this->Form->end();
    ?>

</div>
