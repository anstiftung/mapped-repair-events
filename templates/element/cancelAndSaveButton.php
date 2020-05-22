<div class="cancel-and-save-button-wrapper">

    <button type="submit" class="rounded">
        <?php echo isset($saveLabel) ? $saveLabel : 'Speichern'; ?>
    </button>

    <?php if (!isset($hideCancelButton) || !$hideCancelButton) { ?>
        <button id="cancel-button" type="button" class="rounded gray">
            Abbrechen
        </button>
    <?php } ?>

</div>