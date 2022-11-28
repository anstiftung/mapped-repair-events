<div class="ViewsWrapper">

<?php

use Cake\Core\Configure;

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

    echo '<br />';
    echo '<div class="categories-checkbox-wrapper">';
        echo $this->Form->control('Users.categories._ids', [
            'multiple' => 'checkbox',
            'label' => 'Reparatur-Kenntnisse',
            'id' => 'UserRegCategory' . $userGroup,
        ]);
    echo '</div>';

    echo '<div class="skills-wrapper">';
        echo '<b>Weitere Kenntnisse / Interessen</b>';
        echo $this->Form->control('Users.skills._ids', [
            'multiple' => 'select',
            'data-tags' => true,
            'data-token-separators' => "[',']",
            'label' => false,
            'id' => 'UserRegSkills' . $userGroup,
            'options' => $skillsForDropdown,
        ]);
    echo '</div>
    <br />
    <div id="cbskillsxtra">';

    echo $this->Form->control('Users.about_me', [
        'type' => 'textarea',
        'label' => 'Über mich (max. 1.000 Zeichen)',
        'id' => null
    ]);
    echo '</div>';

    echo '<a class="newsletter" href="'.Configure::read('AppConfig.externNewsletterUrl').'" target="_blank"><i class="fas fa-arrow-right"></i> Hier den Netzwerk-Newsletter abonnieren.</a>';

    echo $this->Form->control('Users.privacy_policy_accepted', [
        'label' => ' Ich habe die <a href="'.$this->Html->urlPageDetail('datenschutz').'" target="_blank">Datenschutzerklärung</a> gelesen und stimme dieser zu.',
        'escape' => false,
        'type' => 'checkbox',
        'id' => null
    ]);

    ?>

    <?php if (!$isCalledByTestSuite) { ?>

        <div class="captcha-wrapper">
            <img src="<?php echo $captchaBuilder->inline(); ?>" />

            <?php

            echo $this->Form->control('Users.reload_captcha', [
                'label' => 'Captcha neu laden?',
                'onclick' => "$(this).closest('form').submit();$(this).closest('.checkbox').css('opacity', 0.5);",
                'type' => 'checkbox',
                'id' => null,
            ]);
            echo $this->Form->control('Users.captcha', [
                'label' => '',
                'placeholder' => 'Captcha bitte hier eintippen.',
                'type' => 'text',
                'id' => null,
            ]);

            ?>
        </div>

    <?php } ?>

    <div class="sc"></div>
    <br />

    <?php

        echo $this->Form->button(__('Submit Form'), [
            'type' => 'submit',
            'class' => 'button',
            'style' => 'padding: 15px 25px;'
        ]);

        echo $this->Form->end();

    ?>

</div>