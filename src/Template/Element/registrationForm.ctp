<div class="ViewsWrapper">

<?php
    echo $this->element('formAntiSpam', [
        'formId' => 'UserReg' . $userGroup,
        'table' => 'User',
        'antiSpamComponent' => $antiSpamComponent
    ]);
    echo $this->element('highlightNavi', [
        'main' => 'Registrieren'
    ]);
    
    echo $this->Form->create($user, [
        'novalidate' => 'novalidate',
        'url' => $url,
        'class' => 'user-reg',
        'id' => 'UserReg' . $userGroup
    ]);
    
    echo $this->Form->control('Users.nick', [
        'label' => __('Register: Your Nickname'),
        'required' => true,
        'id' => null
    ]);
    
    echo $this->Form->control('Users.email', [
        'label' => __('Register: Your Email'),
        'required' => true,
        'id' => null
    ]);
    
    echo $this->Form->control('Users.firstname', [
        'label' => __('Register: Firstname'),
        'required' => true,
        'id' => null
    ]);
    
    echo $this->Form->control('Users.lastname', [
        'label' => __('Register: Lastname'),
        'required' => true,
        'id' => null
    ]);
    
    echo $this->Form->control('Users.street', [
        'label' => __('Register: Street and Number'),
        'id' => null
    ]);
    
    echo $this->Form->control('Users.zip', [
        'label' => __('Register: Your zip code'),
        'required' => true,
        'id' => null
    ]);
    
    echo $this->Form->control('Users.city', [
        'label' => __('Register: City'),
        'id' => null
    ]);
    
    echo '<div class="groups-wrapper">';
    echo $this->Form->control('Users.groups._ids', [
        'id' => null
    ]);
    echo '</div>';
    
    echo $this->Form->control('Users.country_code', [
        'type' => 'select',
        'style' => 'width: 95%;',
        'options' => $countries,
        'default' => 'DE',
        'label' => __('Register: Country'),
        'id' => null
    ]);
    
    echo '<br>';
    echo '<div class="categories-checkbox-wrapper">';
        echo $this->Form->control('Users.categories._ids', [
            'multiple' => 'checkbox',
            'label' => 'Reparatur-Kenntnisse',
            'id' => null
        ]);
    echo '</div>
    <div class="sc"></div> 
    <br>
    <div id="cbskillsxtra">';
    
    echo $this->Form->control('Users.about_me', [
        'type' => 'textarea',
        'label' => 'Über mich (max. 1.000 Zeichen)',
        'id' => null
    ]);
    echo '</div>';
    
    echo $this->Form->control('Users.i_want_to_receive_the_newsletter', [
        'label' => 'Ich möchte den Netzwerk-Newsletter erhalten.',
        'escape' => false,
        'type' => 'checkbox',
        'id' => null
    ]);
    
    echo $this->Form->control('Users.privacy_policy_accepted', [
        'label' => ' Ich habe die <a href="'.$this->Html->urlPageDetail('datenschutz').'" target="_blank">Datenschutzerklärung</a> gelesen und stimme dieser zu.',
        'escape' => false,
        'type' => 'checkbox',
        'id' => null
    ]);
    
    ?>
      
	<div class="sc"></div>
		<br>

		<button class="button" type="submit" style="padding: 15px 25px;"><?php echo __('Submit Form'); ?></button>

	</form>

</div>