<?php
	use App\Controller\Component\StringComponent;
?>

<div class="public-user-wrapper">
	<div class="left-wrapper">
		<?php
    		if ($linkToProfile) {
    		    echo '<a title="Zum Profil von '.h($user->nick).'" href="'.$this->Html->urlUserProfile($user->uid).'">';
    		}
    		echo $this->Html->getUserProfileImage($user);
    		if ($linkToProfile) {
    		    echo '</a>';
    		}
		?>
	</div>
	<div class="right-wrapper">
		<?php
		    echo '<div class="public-name-wrapper">';
    		    if ($user->firstname . ' ' . $user->lastname != $user->nick) {
	   	           echo $user->firstname . ' ' . $user->lastname;
    		    }
		    echo '</div>';
		    
		    echo '<' . $headingTag . '>';
		    if ($linkToProfile) {
		        echo '<a title="Zum Profil von '.h($user->nick).'" href="'.$this->Html->urlUserProfile($user->uid).'">';
		    }
		    echo  $user->nick;
		    if ($linkToProfile) {
		        echo '</a>';
		    }
		    echo '</' . $headingTag . '>';
		    
		    if ($user->email) {
		        echo StringComponent::hide_email($user->email, 'button gray');
		    }
		    
            echo '<div class="address-wrapper">';
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
            echo '</div>';
        ?>
	</div>
</div>
