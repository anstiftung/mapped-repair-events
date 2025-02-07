<?php
declare(strict_types=1);

use App\Model\Entity\Funding;

$this->element('addScript', ['script' =>
    JS_NAMESPACE.".Helper.bindCancelButton();".
    JS_NAMESPACE.".Helper.layoutEditButtons();
"]);
?>

<div class="admin edit">

<?php
    echo $this->Form->create($funding, [
        'novalidate' => 'novalidate',
        'id' => 'fundingForm',
    ]);
    echo $this->element('heading', ['first' => 'Verwendungsnachweis (UID: ' . $funding->uid . ') ' . $funding->workshop->name]);

    echo $this->Form->hidden('referer', ['value' => $referer]);
    $this->Form->unlockField('referer');

    echo '<div class="flexbox">';

        echo '<fieldset>';
            echo '<legend>Status Verwendungsnachweis</legend>';
            echo $this->element('funding/status/usageproofStatus', ['funding' => $funding, 'additionalTextBefore' => '']);
            echo $this->Form->control('Fundings.usageproof_status', ['label' => 'Status', 'options' => Funding::STATUS_MAPPING_FOR_ADMIN_DROPDOWN, 'class' => 'no-verify']);
            echo $this->Form->control('Fundings.usageproof_comment', ['label' => 'Kommentar', 'class' => 'no-verify']);
        echo '</fieldset>';

    echo '</div>';

    echo $this->element('cancelAndSaveButton', ['saveLabel' => 'Speichern']);
    echo $this->Form->end();
?>

</div>
