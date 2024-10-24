<?php
echo $this->element('jqueryTabsWithoutAjax', [
    'links' => $this->Html->getUserBackendNaviLinks($loggedUser->uid, true, $loggedUser->isOrga()),
    'selected' => $this->Html->urlFunding(),
]);
?>

<div class="profile ui-tabs custom-ui-tabs ui-widget-content">
    <div class="ui-tabs-panel">
        <?php echo $this->element('heading', ['first' => $metaTags['title']]); ?>

        <?php echo $workshop->name; ?>

        <?php
            echo '<br /><br />';
            echo $this->Html->link(
                'Zurück zur Übersicht',
                $this->Html->urlFunding(),
                [
                    'title' => 'Zurück zur Übersicht',
                    'class' => 'button',
                ]
            );
        ?>

    </div>
</div>