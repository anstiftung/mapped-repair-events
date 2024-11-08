<?php

if (!empty($orgaTeam)) {
    echo '<div>' . (count($orgaTeam) == 1 ? 'OrganisatorIn' : 'OrganisatorInnen') . ': ';
    foreach ($orgaTeam as $orgaUser) {
        $name = $orgaUser->name . ' (' . $orgaUser->nick . ')';
        if ($orgaUser->status == APP_ON) {
            echo $this->Html->link(
                $name,
                $this->Html->urlUserProfile($orgaUser->uid),
                [
                    'target' => '_blank',
                    'class' => 'orga-user',
                ],
            );
        } else {
            echo '<span class="orga-user">' . $name . '</span>';
        }
    }
    echo '</div>';
}
