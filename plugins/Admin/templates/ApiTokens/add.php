<?php
declare(strict_types=1);

use App\Model\Entity\ApiToken;

$this->element('addScript', ['script' =>
    JS_NAMESPACE . ".Helper.bindCancelButton();".
    JS_NAMESPACE . ".Helper.layoutEditButtons();"
]);
echo $this->element('highlightNavi', ['main' => 'API Tokens']);

$isEditMode = !$apiToken->isNew();
?>

<div class="admin edit">
    <div class="edit">
        <?php echo $this->element('heading', ['first' => $isEditMode ? 'API Token bearbeiten' : 'Neuer API Token']); ?>

        <?php
        echo $this->Form->create($apiToken, ['novalidate']);
        echo $this->Form->hidden('referer', ['value' => $referer]);
        $this->Form->unlockField('referer');

        echo $this->Form->control('status', [
            'type' => 'checkbox',
            'label' => 'Aktiv',
        ]) . '<br />';

        echo $this->Form->control('name', [
            'label' => 'Name',
            'required' => true,
        ]) . '<br />';

        echo $this->Form->control('type', [
            'type' => 'select',
            'label' => 'Typ',
            'options' => ApiToken::TYPES,
            'default' => ApiToken::TYPE_WORKSHOPS,
            'required' => true,
        ]) . '<br />';

        echo $this->Form->control('allowed_search_terms', [
            'type' => 'textarea',
            'label' => 'Erlaubte Suchbegriffe',
            'placeholder' => "Berlin\nMünchen\nHamburg",
            'rows' => 5,
        ]) . '<br />';

        echo $this->Form->control('allowed_domains', [
            'type' => 'textarea',
            'label' => 'Erlaubte Domains (eine Domain pro Zeile), ohne https:// und ohne Pfad',
            'placeholder' => "reparatur-initiativen.de",
            'rows' => 5,
        ]) . '<br />';

        echo $this->Form->control('expires_at', [
            'type' => 'datetime',
            'label' => 'Ablaufdatum (optional)',
            'empty' => true,
        ]) . '<br />';

        if ($isEditMode) {
            echo '<div class="info-box">';
            echo '<strong>Token:</strong> ' . h($apiToken->token) . '<br />';
            echo '<strong>Zuletzt verwendet:</strong> ' . ($apiToken->last_used ? $apiToken->last_used->nice() : 'Noch nicht verwendet') . '<br />';
            echo '</div><br />';
        } else {
            echo '<div class="info-box">';
                echo 'Der neue Token wird automatisch beim Speichern generiert.';
            echo '</div>';
        }
        ?>
    </div>

    <?php echo $this->element('cancelAndSaveButton'); ?>

    <?php echo $this->Form->end(); ?>
</div>

<div class="sc"></div>
