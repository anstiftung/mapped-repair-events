<?php

use Cake\Core\Configure;
use App\Model\Entity\Funding;
echo $this->element('jqueryTabsWithoutAjax', [
    'links' => $this->Html->getUserBackendNaviLinks($loggedUser->uid, true, $loggedUser->isOrga())
]);
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
                echo '<div class="workshop-wrapper">';
                    echo $this->Html->link(
                        $workshop->name,
                        $this->Html->urlWorkshopDetail($workshop->url),
                        [
                            'class' => 'heading',
                        ],
                    );
                    echo '<div class="table">';
                        $classes = ['button'];
                        if ($workshop->funding_created_by_different_owner) {
                            $classes[] = 'disabled';
                        }
                        echo $this->Html->link(
                            $workshop->funding_exists ? 'Förderantrag bearbeiten' : 'Förderantrag erstellen',
                            $workshop->funding_created_by_different_owner ? 'javascript:void(0);' : $this->Html->urlFundingsEdit($workshop->uid),
                            [
                                'class' => implode(' ', $classes),
                            ],
                        );
                        echo '<div>';
                            if ($workshop->funding_exists) {
                                echo '<div>UID: ' . $workshop->funding->uid. ' / ' . $workshop->funding->verified_fields_count . ' von ' . Funding::getFieldsCount() . ' Feldern bestätigt</div>';
                            }
                            echo $this->element('funding/owner', ['funding' => $workshop->funding]);
                            echo $this->element('funding/orgaTeam', ['orgaTeam' => $workshop->orga_team]);
                        echo '</div>';
                    echo '</div>';
                echo '</div>';
                echo '<div class="dotted-line"></div>';
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
                echo '<div class="workshop-wrapper">';
                    echo $this->Html->link(
                        $workshop->name,
                        $this->Html->urlWorkshopDetail($workshop->url),
                        [
                            'class' => 'heading',
                            'target' => '_blank',
                        ],
                    );
                    echo '<div class="table">';
                        echo $button;
                        echo '<div>';
                            echo $this->element('funding/orgaTeam', ['orgaTeam' => $workshop->orga_team]);
                            foreach($workshop->funding_errors as $error) {
                                echo '<div><i>' . $error . '</i></div>';
                            }
                            if ($workshop->funding_is_country_code_ok) {
                                echo '<div>' . $this->Html->link(
                                    'Termin erstellen',
                                    $this->Html->urlEventNew($workshop->uid),
                                    [
                                        'target' => '_blank',
                                    ],
                                ) . '</div>';
                            }
                        echo '</div>';
                    echo '</div>';
                echo '</div>';
                echo '<div class="dotted-line"></div>';
            }
        ?>

    </div>

</div>