<?php
declare(strict_types=1);

if (!empty($funding->owner_user)) {
    echo '<div>Förderantrag erstellt von: ';
        $name = $funding->owner_user->name . ' (' . $funding->owner_user->nick . ')';
        if ($funding->owner_user->status == APP_ON) {
            echo $this->Html->link(
                $name,
                $this->Html->urlUserProfile($funding->owner_user->uid),
                [
                    'target' => '_blank',
                    'class' => 'orga-user',
                ],
            );
        } else {
            echo '<span class="orga-user">' . $name . '</span>';
        }
    echo '</div>';
}
