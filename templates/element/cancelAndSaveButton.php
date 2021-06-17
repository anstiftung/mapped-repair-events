<div class="cancel-and-save-button-wrapper">

    <button type="submit" name="save-button" class="rounded">
        <?php echo isset($saveLabel) ? $saveLabel : 'Speichern'; ?>
    </button>

    <?php if (isset($showSaveAndRedirectToUrlButton) && !empty($showSaveAndRedirectToUrlButton)) { ?>
        <button id="save-and-redirect-to-url-button" name="save-and-redirect-to-url-button" type="button" class="rounded" data-redirect-url="<?php echo $showSaveAndRedirectToUrlButton['redirectUrl']; ?>">
            <?php echo $showSaveAndRedirectToUrlButton['label']; ?>
        </button>
    <?php } ?>

    <?php if (!isset($hideCancelButton) || !$hideCancelButton) { ?>
        <button id="cancel-button" type="button" class="rounded gray">
            Abbrechen
        </button>
    <?php } ?>

</div>