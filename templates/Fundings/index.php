<?php

use Cake\Core\Configure;
echo $this->element('jqueryTabsWithoutAjax', [
    'links' => $this->Html->getUserBackendNaviLinks($loggedUser->uid, true, $loggedUser->isOrga())
]);
$fundingsCriteria = [
    'Initiative muss aus Deutschland sein',
    'UND',
    'Initiative muss vor dem ' . date('d.m.Y', strtotime(Configure::read('AppConfig.fundingsStartDate'))) . ' registriert worden sein UND mindestens einen vergangenen Termin haben',
    'ODER',
    'Einen bestätigten Aktivitätsnachweis UND mindestens 4 zukünftige Termine haben',
];
$fundingsCriteria = implode("\\n", $fundingsCriteria);
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
                    } else {
                        echo $this->Html->link(
                            'Förderantrag nicht möglich',
                            'javascript:alert("' . $fundingsCriteria . '");',
                            [
                                'title' => 'Förderantrag nicht möglich',
                                'disabled' => 'disabled',
                                'class' => 'button disabled'
                                ]
                        );
                    }
                    echo '<span>' . $workshop->name . '</span>';
                echo '</div>';
            }
        ?>
    </div>

</div>