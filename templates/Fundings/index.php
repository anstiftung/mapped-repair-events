<?php

use Cake\Core\Configure;
echo $this->element('jqueryTabsWithoutAjax', [
    'links' => $this->Html->getUserBackendNaviLinks($loggedUser->uid, true, $loggedUser->isOrga())
]);
?>

<div class="profile ui-tabs custom-ui-tabs ui-widget-content">
    <div class="ui-tabs-panel">

        <?php echo $this->element('heading', ['first' => $metaTags['title']]); ?>

        <?php if ($loggedUser->isAdmin()) { ?>
            <p>Möglich: <?php echo $this->Number->precision(count($workshopsWithFundingAllowed), 0); ?>x</p>
            <p>Nicht möglich: <?php echo $this->Number->precision(count($workshopsWithFundingNotAllowed), 0); ?>x</p>
            <br />
        <?php } ?>

        <?php
            foreach($workshopsWithFundingAllowed as $workshop) {
                echo '<div class="workshop-wrapper">';
                    echo $this->Html->link(
                        $workshop->funding_exists ? 'Förderantrag bearbeiten' : 'Förderantrag erstellen',
                        $this->Html->urlFundingsEdit($workshop->uid),
                        [
                            'class' => 'button',
                        ],
                    );
                    echo '<span>';
                        echo '<a href="' . $this->Html->urlWorkshopDetail($workshop->url) . '">' . $workshop->name . '</a>';
                    echo '</span>';
                echo '</div>';
            }

            foreach($workshopsWithFundingNotAllowed as $workshop) {
                $button = $this->Html->link(
                    'Förderantrag nicht möglich',
                    'javascript:void(0);',
                    [
                        'disabled' => 'disabled',
                        'class' => 'button disabled',
                    ],
                );
                if (!$workshop->funding_was_registered_before_fundings_start_date) {
                    if (empty($workshop->funding) || $workshop->funding->activity_proof_filename == '') {
                        $button = $this->Html->link(
                            'Aktivitätsnachweis hochladen',
                            $this->Html->urlFundingsUploadActivityProof($workshop->uid),
                            [
                                'class' => 'button',
                            ],
                        );
                    }
                    if (!empty($workshop->funding) && $workshop->funding->activity_proof_filename != '' && !$workshop->funding->activity_proof_ok) {
                        $button = $this->Html->link(
                            'Aktivitätsnachweis ungeprüft',
                            'javascript:void(0);',
                            [
                                'disabled' => 'disabled',
                                'class' => 'button disabled',
                            ],
                        );
                    }
                }
                echo '<div class="workshop-wrapper">';
                    echo $button;
                    echo '<span>';
                        echo '<a href="' . $this->Html->urlWorkshopDetail($workshop->url) . '">' . $workshop->name . '</a>';
                        echo ' <i>' . implode('', $workshop->funding_errors) . '</i>';
                    echo '</span>';
                echo '</div>';
            }
        ?>

    </div>

</div>