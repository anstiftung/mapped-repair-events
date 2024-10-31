<?php

$this->element('addScript', array('script' =>
    JS_NAMESPACE.".Helper.bindCancelButton();".
    JS_NAMESPACE.".Helper.layoutEditButtons();
"));
?>

<div class="admin edit">

    <?php
    echo $this->Form->create($funding, [
        'novalidate' => 'novalidate',
    ]);
    echo '<div style="padding: 0 20px;">';
        echo $this->element('heading', ['first' => 'Förderantrag bearbeiten: ' . $funding->workshop->name]);
    echo '</div>';
    ?>
    <div class="edit">
        <?php
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
