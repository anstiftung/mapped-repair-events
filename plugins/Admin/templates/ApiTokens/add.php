<?php
declare(strict_types=1);

$this->element('addScript', ['script' =>
    JS_NAMESPACE . ".Helper.bindCancelButton();".
    JS_NAMESPACE . ".Helper.layoutEditButtons();"
]);
echo $this->element('highlightNavi', ['main' => 'API Tokens']);
?>

<div class="admin edit">
    <div class="edit">
        <?php echo $this->element('heading', ['first' => 'Neues API Token']); ?>

        <?php
        echo $this->Form->create($apiToken, ['novalidate']);
        echo $this->Form->hidden('referer', ['value' => $referer]);
        $this->Form->unlockField('referer');

        echo $this->Form->control('name', [
            'label' => 'Name',
            'required' => true,
        ]) . '<br />';

        echo $this->Form->control('allowed_search_terms', [
            'type' => 'textarea',
            'label' => 'Erlaubte Suchbegriffe (ein Begriff pro Zeile, z.B. Berlin, München)',
            'placeholder' => "Berlin\nMünchen\nHamburg",
            'rows' => 5,
        ]) . '<br />';

        echo $this->Form->control('expires_at', [
            'type' => 'datetime',
            'label' => 'Ablaufdatum (optional)',
            'empty' => true,
        ]) . '<br />';

        echo $this->Form->control('is_active', [
            'type' => 'checkbox',
            'label' => 'Aktiv',
            'checked' => true,
        ]) . '<br />';

        echo '<div class="info-box">';
        echo '<strong>Wichtig:</strong> Ein neues, zufälliges Token wird automatisch beim Speichern generiert. ';
        echo 'Das Token wird nur einmal angezeigt - bitte speichern Sie es sicher!';
        echo '</div>';
        ?>
    </div>

    <?php echo $this->element('cancelAndSaveButton'); ?>

    <?php echo $this->Form->end(); ?>
</div>

<div class="sc"></div>
