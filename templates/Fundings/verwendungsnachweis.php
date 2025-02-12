<?php
declare(strict_types=1);

use App\Model\Entity\Funding;

$this->element('addScript', ['script' =>
    JS_NAMESPACE.".Helper.bindCancelButton();".
    JS_NAMESPACE.".Funding.initIsMissing();".
    JS_NAMESPACE.".Funding.initTextareaCounter();"
]);
echo $this->element('jqueryTabsWithoutAjax', [
    'links' => $this->Html->getUserBackendNaviLinks($loggedUser->uid, true, $loggedUser->isOrga()),
    'selected' => $this->Html->urlFundings(),
]);
?>

<div class="profile ui-tabs custom-ui-tabs ui-widget-content">
    <div class="ui-tabs-panel">
        <?php
            echo $this->element('heading', ['first' => $metaTags['title']]);
            echo $this->element('funding/blocks/usageproof/blockMainInfo');
            echo $this->element('funding/blocks/blockColorCodes');

            echo $this->Form->create($funding, [
                'novalidate' => 'novalidate',
                'url' => $this->Html->urlFundingsUsageproof($funding->uid),
                'id' => 'fundingForm',
            ]);

                echo $this->Form->hidden('referer', ['value' => $referer]);
                $this->Form->unlockField('referer');
                $this->Form->unlockField('add_receiptlist');

                echo '<div class="flexbox">';
                    echo $this->element('funding/blocks/usageproof/blockSachbericht', ['funding' => $funding, 'disabled' => false]);
                echo '</div>';

                echo $this->element('funding/status/usageproofStatus', [
                    'funding' => $funding,
                    'additionalTextBefore' => 'Admin-Status: ',
                ]);

                echo '<div class="flexbox">';
                    echo $this->element('funding/blocks/usageproof/blockReceiptlist', ['funding' => $funding, 'disabled' => false]);
                    echo $this->element('funding/blocks/usageproof/blockCheckboxes', ['funding' => $funding, 'disabled' => false]);
                echo '</div>';

                echo $this->element('cancelAndSaveButton', [
                    'saveLabel' => 'Zwischenspeichern',
                ]);

                echo '<div class="flexbox" style="margin-top:20px;">';
                    echo '<fieldset>';
                        echo '<legend>Kostenplan</legend>';
                        echo $this->element('funding/blocks/blockBudgetplanGrouped', ['funding' => $funding]);
                    echo '</fieldset>';
                echo '</div>';

            echo $this->Form->end();

        ?>

    </div>
</div>