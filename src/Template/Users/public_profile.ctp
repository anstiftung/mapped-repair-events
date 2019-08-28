<?php
use App\Controller\Component\StringComponent;
?>

<div class="top-wrapper">
	<?php echo $this->element('users/publicUser', ['user' => $user, 'headingTag' => 'h1', 'linkToProfile' => false]); ?>
</div>

<div class="bottom-wrapper">
    <?php if ($user->about_me != '' || !empty($user->skills)) { ?>
    	<div class="about-me-wrapper">
    		<b>Mein Profil</b>
    		<div class="about-me-content">
    			<?php
        			if ($user->about_me != '') {
        			    echo '<p style="margin-bottom:10px;">' . $user->about_me . '</p>';
        			}
    			    if (!empty($user->skills)) {
                        foreach($user->skills as $skill) {
                            echo '<a href="'.$this->Html->urlSkillDetail($skill->id, StringComponent::slugify($skill->name)).'" title="'.h($skill->name).'" class="button">'.h($skill->name).'</a>';
                        }
    			    }
                ?>
    		</div>
    	</div>
    	<div class="sc"></div>
    	<br />
    <?php }?>
    
    <?php if(!empty($user->categories)) { ?>
    	<strong><?php echo __('Skills from this user'); ?> </strong>
    	<div class="sc"></div>
    
        <div id="sklill_icons">
            <?php 
                foreach($user->categories as $category) {
                    echo '<div title="'.h($category->name).'" class="sklill_icon small '.h($category->icon).'"></div>';
                }
            ?>
        </div>
    <?php } ?>
    
    <?php
        $additionalContactPossibilities = [];
        if ($user->additional_contact) {
    	   $additionalContactPossibilities[] = $user->additional_contact;
        }
        if ($user->website) {
            $additionalContactPossibilities[] = $user->website;
        }
        if ($user->phone) {
            $additionalContactPossibilities[] = $user->phone;
        }
        $additionalSocial = [];
        if ($user->facebook_username) {
            $additionalSocial[] = '<a href="' . $this->Html->getFacebookUrl($user->facebook_username) . '" target="_blank">
                <i class="fab fa-facebook-square"></i>
            </a>';
        }
        if ($user->twitter_username) {
            $additionalSocial[] = '<a href="https://twitter.com/' . $user->twitter_username . '" target="_blank">
                <i class="fab fa-twitter-square"></i>
            </a>';
        }
        if ($user->feed_url) {
            $additionalSocial[] = '<a href="' . $user->feed_url. '" target="_blank">
                <i class="fas fa-rss-square"></i>
            </a>';
        }
        if (!empty($additionalSocial)) {
            $additionalContactPossibilities[] = implode('', $additionalSocial);
        }
        
        if (!empty($additionalContactPossibilities)) {
	    echo '<b style="margin-bottom: 5px;">' . __('More contact possibilities') . '</b>';
        echo '<ul><li>' . implode('</li><li>', $additionalContactPossibilities) . '</li></ul>';
    }
    
    if (!empty($user->workshops)) {
        echo '<strong>';
            echo __('This user is working for the following Repair Initiatives');
        echo '</strong><br />';
    ?>
        <div class="workshop-link-wrapper">
    		<?php foreach($user->workshops as $workshop) { ?>
            	<a class="button gray workshop" href="<?php echo $this->Html->urlWorkshopDetail($workshop->url); ?>">
            		<?php echo $workshop->name; ?>
            	</a>
            <?php } ?>
    	</div>
    <?php } ?>
</div>