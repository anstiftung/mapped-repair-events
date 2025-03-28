<?php
declare(strict_types=1);
use App\Model\Entity\Funding;
?>

<fieldset class="full-width">
    <legend><?php echo Funding::FIELDS_FUNDING_DATA_CHECKBOXES_LABEL; ?></legend>
    <?php
        echo '<div class="verification-wrapper ' . $funding->checkboxes_status_css_class . '">';
            echo '<p>' . $funding->checkboxes_status_human_readable . '</p>';
        echo '</div>';
    ?>
    <?php
        echo Funding::getRenderedFields(Funding::FIELDS_FUNDING_DATA_CHECKBOXES, 'fundingdata', $this->Form, $disabled);
    ?>

</fieldset>
