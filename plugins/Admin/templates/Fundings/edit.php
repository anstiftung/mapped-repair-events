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
        'id' => 'fundingForm',
    ]);
    echo '<div style="padding: 0 20px;">';
        echo $this->element('heading', ['first' => 'Förderantrag bearbeiten: ' . $funding->workshop->name]);
    echo '</div>';
    ?>
    <div class="edit">
        <?php

            echo $this->Form->hidden('referer', ['value' => $referer]);
            $this->Form->unlockField('referer');

            $activityProofFilenameLabel = 'Datei (' . $this->Html->link('anzeigen', $this->Html->urlFundingsActivityProofDetail($funding->id), ['target' => '_blank']) . ')';
            echo $this->Form->fieldset(
                $this->Form->control('Fundings.activity_proof_filename', ['label' => $activityProofFilenameLabel, 'escape' => false]).
                $this->Form->control('Fundings.activity_proof_ok', ['label' => 'Geprüft']),
                [
                    'legend' => 'Aktivitätsnachweis',
                ]
            );

        ?>
    </div>

    <?php
        echo $this->element('cancelAndSaveButton', ['saveLabel' => 'Speichern']);
        echo $this->Form->end();
    ?>

</div>
