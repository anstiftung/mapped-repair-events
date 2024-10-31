<?php
use Cake\Core\Configure;

$this->element('addScript', array('script' =>
    JS_NAMESPACE.".Helper.bindCancelButton();".
    JS_NAMESPACE.".Helper.layoutEditButtons();
"));
?>

<div class="admin edit">

    <?php
    echo $this->Form->create($funding, [
        'novalidate' => 'novalidate'
    ]);
    ?>
    <div class="edit">
        <?php
            echo $this->element('heading', ['first' => $metaTags['title']]);
            echo $this->Form->hidden('referer', ['value' => $referer]);
            $this->Form->unlockField('referer');
            echo $this->Form->control('Fundings.activity_proof_ok', ['label' => 'Aktivitätsnachweis OK?']).'<br />';
        ?>
    </div>

    <?php
        echo $this->element('cancelAndSaveButton', ['saveLabel' => 'Speichern']);
        echo $this->Form->end();
    ?>

</div>
