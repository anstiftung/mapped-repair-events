<?php
declare(strict_types=1);
use App\Model\Entity\Funding;
?>

<fieldset class="full-width">
    <legend><?php echo Funding::FIELDS_USAGEPROOF_CHECKBOXES_LABEL; ?></legend>
    <?php
        echo '<div class="verification-wrapper ' . $funding->usageproof_checkboxes_status_css_class . '">';
            echo '<p>' . $funding->usageproof_checkboxes_status_human_readable . '</p>';
        echo '</div>';
    ?>
    <?php
        echo Funding::getRenderedFields(Funding::FIELDS_USAGEPROOF_CHECKBOXES, 'fundingusageproof', $this->Form, $disabled);
    ?>

</fieldset>
