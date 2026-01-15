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
        <?php echo $this->element('heading', ['first' => 'API Token bearbeiten']); ?>

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
        ]) . '<br />';

        echo '<div class="info-box">';
        echo '<strong>Token:</strong> ' . h(substr($apiToken->token, 0, 16)) . '... (aus Sicherheitsgründen nicht vollständig angezeigt)<br />';
        echo '<strong>Zuletzt verwendet:</strong> ' . ($apiToken->last_used ? $apiToken->last_used->nice() : 'Noch nicht verwendet') . '<br />';
        echo '</div><br />';

        ?>
    </div>

    <?php echo $this->element('cancelAndSaveButton'); ?>

    <?php echo $this->Form->end(); ?>
</div>

<div class="sc"></div>
