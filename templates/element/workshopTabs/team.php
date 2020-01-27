<?php if (!$this->request->getSession()->read('isMobile')) { ?>
    <div id="tabs-2">

    <?php
    if (count($team) > 1) {
        foreach ($team as $user) {

            echo '<div class="orgaansprech">';
                echo '<a class="green" title="Zum Profil" href="'.$this->Html->urlUserProfile($user->uid) . '">';
                    echo $this->Html->getUserProfileImage($user);
                    echo '<b style="color:#727374;">' . __('CONTACT PERSON') . '</b><br />';
                    if ($user->firstname != '' || $user->lastname != '') {
                        if ($user->firstname != '') {
                            echo $user->firstname . ' ';
                        }
                        if ($user->lastname != '') {
                            echo $user->lastname;
                        }
                    } else {
                        echo $user->nick;
                    }
                    echo '<br />';
                echo '</a>';
    
                $groupNames = [];
                if (isset($user->groups)) {
                    foreach($user->groups as $group) {
                        $groupNames[] = $groups[$group['id']];
                    }
                }
                if (!empty($groupNames)) {
                    echo '<small>('.implode(', ', $groupNames).')</small>';
                }
            echo '</div>';
        }
    }
    ?>

    </div>
    
<?php } ?>