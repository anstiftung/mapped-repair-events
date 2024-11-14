<?php

use App\Model\Entity\Funding;

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