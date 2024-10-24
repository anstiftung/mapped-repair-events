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
            <p>Möglich: <?php echo $workshopsWithFundingAllowed; ?>x</p>
            <p>Nicht möglich: <?php echo $workshopsWithFundingNotAllowed; ?>x</p>
            <br />
        <?php } ?>

        <?php
            foreach($workshops as $workshop) {
                echo '<div class="workshop-wrapper">';
                    if ($workshop->funding_is_allowed) {
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
                            'javascript:void(0);',
                            [
                                'title' => 'Förderantrag nicht möglich',
                                'disabled' => 'disabled',
                                'class' => 'button disabled'
                                ]
                        );
                    }
                    echo '<span>';
                        echo '<a href="' . $this->Html->urlWorkshopDetail($workshop->url) . '">' . $workshop->name . '</a>';
                        if (!$workshop->funding_is_allowed) {
                            echo ' <i>' . implode(' / ', $workshop->funding_errors) . '</i>';
                        }
                    echo '</span>';
            echo '</div>';
            }
        ?>

    </div>

</div>