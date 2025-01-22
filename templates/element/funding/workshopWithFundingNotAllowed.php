<?php
declare(strict_types=1);

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
