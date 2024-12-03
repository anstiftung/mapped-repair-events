<?php
    use App\Model\Entity\Funding;
?>

<fieldset>
    <legend><?php echo Funding::FIELDS_WORKSHOP_LABEL; ?> (UID: <?php echo $funding->workshop->uid; ?>)</legend>
    <?php if (!$disabled) { ?>
        <p style="margin-bottom:10px;padding:5px;">
            Ist die hier angegebene Adresse der Hauptort der Reparatur-Initiative? Änderungen werden auch auf der Plattform angezeigt.
        </p>
    <?php } ?>
    <?php
        echo Funding::getRenderedFields(Funding::FIELDS_WORKSHOP, 'workshop', $this->Form, $disabled);
    ?>

</fieldset>