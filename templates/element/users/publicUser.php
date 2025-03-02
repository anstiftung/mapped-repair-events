<?php
declare(strict_types=1);
use App\Controller\Component\StringComponent;

if ($linkToProfile) {
    echo '<a class="public-user-wrapper" href="'.$this->Html->urlUserProfile($user->uid).'" title="Zum Profil von '.h($user->nick).'">';
} else {
    echo '<span class="public-user-wrapper">';
}
?>

<span class="left-wrapper">
    <?php
        echo $this->Html->getUserProfileImage($user);
    ?>
</span>
<span class="right-wrapper">
    <?php
        echo '<span class="public-name-wrapper">';
            if ($user->firstname != '' || $user->lastname != '') {
                $name = [];
                if ($user->firstname != '') {
                    $name[] = $user->firstname;
                }
                if ($user->lastname != '') {
                    $name[] = $user->lastname;
                }
                echo join(' ', $name);
            }
        echo '</span>';

        if (!isset($name)) {
            echo '<' . $headingTag . '>';
                echo  $user->nick;
            echo '</' . $headingTag . '>';
        } else {
            echo '<br />';
        }

        if ($user->email) {
            echo StringComponent::hide_email($user->email, 'email', false);
        }

        echo '<span class="address-wrapper">';
            $addressString = '';
            if ($user->street != '') {
                $addressString .= str_replace("\r\n", ', ', $user->street);
            }
            if ($user->zip != '') {
                if ($addressString != '') {
                    $addressString .= ', ';
                }
                $addressString .= $user->zip;
            }
            if ($user->city != '') {
                $addressString .= ' ' . $user->city;
            }
            if ($user->country_code != '') {
                if ($addressString != '') {
                    $addressString .= ' / ';
                }
                $addressString .= $user->country_code;
            }
            echo $addressString;
        echo '</span>';

        if (isset($hasModifyPermissions) && $hasModifyPermissions) {
            echo '<a class="btn edit" href="'.$this->Html->urlUserEdit($user->uid, $isMyProfile).'">';
                echo '<button class="btn" type="submit">Userprofil bearbeiten</button>';
            echo '</a>';
        }

    ?>
</span>
<?php
if ($linkToProfile) {
    echo '</a>';
} else {
    echo '</span>';
}
?>