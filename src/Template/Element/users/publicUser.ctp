<?php
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
		    if ($user->firstname . ' ' . $user->lastname != $user->nick) {
   	           echo $user->firstname . ' ' . $user->lastname;
		    }
	    echo '</span>';
	    
	    echo '<' . $headingTag . '>';
		    echo  $user->nick;
	    echo '</' . $headingTag . '>';
	    
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
    ?>
</span>
<?php
if ($linkToProfile) {
    echo '</a>';
} else {
    echo '</span>';
}
?>