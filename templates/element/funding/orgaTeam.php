<?php

if (!empty($orgaTeam)) {
    echo '<div>' . (count($orgaTeam) == 1 ? 'OrganisatorIn' : 'OrganisatorInnen') . ': ';
    foreach ($orgaTeam as $orgaUser) {
        if ($orgaUser->status == APP_ON) {
            echo $this->Html->link(
                $orgaUser->name,
                $this->Html->urlUserProfile($orgaUser->uid),
                [
                    'target' => '_blank',
                    'class' => 'orga-user',
                ],
            );
        } else {
            echo '<span class="orga-user">' . $orgaUser->name . '</span>';
        }
    }
    echo '</div>';
}
