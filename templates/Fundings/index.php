<?php
echo $this->element('jqueryTabsWithoutAjax', [
    'links' => $this->Html->getUserBackendNaviLinks($loggedUser->uid, true, $loggedUser->isOrga())
]);
?>

<div class="profile ui-tabs custom-ui-tabs ui-widget-content">
    <div class="ui-tabs-panel">
        <?php echo $this->element('heading', ['first' => $metaTags['title']]); ?>
    </div>

    <div>
        <?php
            foreach($workshops as $workshop) {
                echo '<div class="workshop-wrapper">';
                    if ($workshop->is_funding_allowed) {
                        echo $this->Html->link(
                            'Förderantrag bearbeiten',
                            $this->Html->urlFundingDetail($workshop->uid),
                            [
                                'title' => 'Förderantrag bearbeiten',
                                'class' => 'button',
                            ]
                        );
                        echo '<span>' . $workshop->name . '</span>';
                    } else {
                        echo $this->Html->link(
                            'Förderantrag nicht möglich',
                            'javascript:void(0);',
                            [
                                'title' => 'Förderantrag nicht möglich',
                                'disabled' => 'disabled',
                                'class' => 'button disabled'
                            ]
                        );
                        echo '<span>' . $workshop->name . ' ' . '(Förderanträge sind nur für Initiativen aus Deutschland möglich.)</span>';
                    }
                echo '</div>';
            }
        ?>
    </div>

</div>