<?php
declare(strict_types=1);

echo $this->element('jqueryTabsWithoutAjax', [
    'links' => $this->Html->getUserBackendNaviLinks($loggedUser->uid, true, $loggedUser->isOrga())
]);

echo $this->element('funding/fundingEndDateNTimeNotice');

?>

<div class="profile ui-tabs custom-ui-tabs ui-widget-content">
    <div class="ui-tabs-panel">

        <?php echo $this->element('heading', ['first' => $metaTags['title']]); ?>

        <?php if ($loggedUser->isAdmin()) { ?>
            <div class="info-box-counts">
                <p>Möglich: <?php echo $this->Number->precision(count($workshopsWithFundingAllowed), 0); ?>x</p>
                <p>Nicht möglich: <?php echo $this->Number->precision(count($workshopsWithFundingNotAllowed), 0); ?>x</p>
            </div>
        <?php } ?>

        <?php
            foreach($workshopsWithFundingAllowed as $workshop) {
                echo $this->element('funding/workshopWithFundingAllowed', ['workshop' => $workshop]);
            }

            foreach($workshopsWithFundingNotAllowed as $workshop) {
                echo $this->element('funding/workshopWithFundingNotAllowed', ['workshop' => $workshop]);
            }
        ?>

    </div>

</div>