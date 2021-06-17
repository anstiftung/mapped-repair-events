<div class="cancel-and-save-button-wrapper">

    <?php
        $saveLabel = isset($saveLabel) ? $saveLabel : 'Speichern';
        echo $this->Form->button($saveLabel, [
            'type' => 'submit',
            'name' => 'save-button',
            'class' => 'rounded',
        ]);

        if (isset($showSaveAndRedirectToUrlButton) && !empty($showSaveAndRedirectToUrlButton)) {
            echo $this->Form->button($showSaveAndRedirectToUrlButton['label'], [
                'type' => 'button',
                'id' => 'save-and-redirect-to-url-button',
                'name' => 'save-and-redirect-to-url-button',
                'class' => 'rounded',
                'data-redirect-url' => $showSaveAndRedirectToUrlButton['redirectUrl'],
            ]);
        }

        if (!isset($hideCancelButton) || !$hideCancelButton) {
            echo $this->Form->button('Abbrechen', [
                'type' => 'button',
                'id' => 'cancel-button',
                'class' => 'rounded gray',
            ]);
        }
    ?>

</div>