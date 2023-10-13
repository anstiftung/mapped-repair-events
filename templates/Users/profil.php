<?php
use Cake\Core\Configure;

echo $this->element('highlightNavi', [
    'main' => ''
]);

$this->element('addScript', ['script' =>
    JS_NAMESPACE.".User.addPrivateFieldsToUserEdit(".json_encode($user->private_as_array).");".
    JS_NAMESPACE.".AppFeatherlight.initLightboxForHref('a.open-with-featherlight');".
    JS_NAMESPACE.".Helper.bindCancelButton(".$user->uid.");
"]);
if ($isMyProfile) {
    echo $this->element('jqueryTabsWithoutAjax', [
            'links' => $this->Html->getUserBackendNaviLinks($user->uid, $isMyProfile, $loggedUser->isOrga())
        ]
    );
}
?>
<div class="profile ui-tabs custom-ui-tabs ui-widget-content">
    <div class="ui-tabs-panel">

        <?php echo $this->element('heading', ['first' => $metaTags['title'] ]); ?>

        <p><?php echo __('Profil: introtext'); ?></p>
        <p><a href="<?php echo $this->Html->urlUserProfile($loggedUser->uid); ?>" target="_blank">Mein öffentliches Profil anzeigen</a><p>
        <br />

        <?php if ($isMyProfile) { ?>
            <input type="submit" value="<?php echo __('Change password'); ?>" class="buttonSubmit gray" onclick="var rd='/users/passwortAendern'; window.location.href=rd; return false;" />
            <div style="clear: both; margin: 2%;"></div>
            <br />
        <?php } ?>

        <?php
            echo $this->Form->create($user, [
                    'novalidate' => 'novalidate',
                    'url' => $isEditMode ? $this->Html->urlUserEdit($user->uid, $isMyProfile) : $this->Html->urlUserNew(),
                    'id' => 'userProfileForm'
                ]
            );

                $this->Form->unlockField('Users.private_as_array');

                echo $this->Form->hidden('referer', ['value' => $referer]);
                $this->Form->unlockField('referer');

                echo $this->element('heading', ['first' => 'Deine Rollen' ]);
                echo $this->element('hint', [
                    'content' => $this->Html->getRoleHint($repairhelperInfotext, $orgaInfotext)
                ]);

            ?>

        <br /><div class="sc"></div>
        <?php
            echo '<div class="groups-checkbox-wrapper">';
                echo $this->Form->control('Users.groups._ids', [
                    'multiple' => 'checkbox',
                    'label' => false,
                    'error' => ['escape' => false]
                ]);
                echo '</div>';
                echo '<div class="sc"></div>';


                echo $this->element('heading', ['first' => 'Profileinstellungen' ]);
                echo '<b class="hint">Datenschutzeinstellungen: Wenn du ein Häkchen im Feld neben deinen Benutzerangaben setzt, sind diese Informationen nicht öffentlich sichtbar. Nur Mitglieder deiner Initiative(n) können deinen Angaben sehen.</b>';
                echo $this->Form->control('Users.nick', ['label' => 'Nickname']).'<br />';
                echo $this->Form->control('Users.firstname', ['label' => 'Vorname', 'data-private' => true]).'<br />';
                echo $this->Form->control('Users.lastname', ['label' => 'Nachname', 'data-private' => true]).'<br />';
                echo $this->Form->control('Users.email', ['label' => 'E-Mail', 'data-private' => true]).'<br />';

                echo '<div class="sc"></div><br /><b class="hint">Hinweis: Das Benutzerbild sollte quadratisch sein und muss mindestens 300 x 300 Pixel messen.</b>';
                echo $this->element('upload/single', [
                    'field' => 'Users.image',
                    'objectType'   => 'users',
                    'image' => $user->image,
                    'uid' => $user->uid,
                    'label' => 'Benutzerbild hochladen'
                ]);
                echo '<br /><div class="sc"></div>';

                echo '<div class="small-textarea">';
                    echo $this->Form->control('Users.street', ['type' => 'textarea', 'label' => 'Anschrift', 'data-private' => true]).'<br />';
                echo '</div>';
                echo $this->Form->control('Users.zip', ['label' => 'PLZ', 'data-private' => true]).'<br />';
                echo $this->Form->control('Users.city', ['label' => 'Stadt', 'data-private' => true]).'<br />';
                echo $this->Form->control('Users.country_code', ['type' => 'select', 'options' => $countries, 'label' => 'Land', 'empty' => '-----------', 'data-private' => true]).'<br />';

                echo $this->Form->control('Users.phone', ['label' => 'Telefon', 'data-private' => true]).'<br />';
                echo $this->Form->control('Users.website', ['label' => 'Website', 'data-private' => true]).'<br />';
                echo $this->Form->control('Users.twitter_username', ['label' => 'Twitter Benutzername', 'data-private' => true]).'<br />';
                echo $this->Form->control('Users.feed_url', ['label' => 'RSS Feed URL', 'data-private' => true]).'<br />';

                echo $this->element('hint', [
                    'content' => $this->Html->getFacebookHint()
                ]);

                echo $this->Form->control('Users.facebook_username', ['label' => 'Facebook Benutzername', 'data-private' => true]).'<br />';
                echo $this->Form->control('Users.additional_contact', ['label' => 'Weitere Kontaktmöglichkeiten', 'type' => 'textarea', 'escape' => false, 'data-private' => true]).'<br />';

                echo '<div class="categories-checkbox-wrapper">';
                    echo '<b id="users-categories" class="pseudo-field" data-private="true" >' . Configure::read('AppConfig.categoriesNameUsers') . '</b>';
                    echo $this->Form->control('Users.categories._ids', [
                        'multiple' => 'checkbox',
                        'label' => false
                    ]);
                echo '</div>';
                echo '<div class="sc"></div>';

                echo '<div class="skills-wrapper">';
                    echo '<b id="users-skills" class="pseudo-field" data-private="true" >Weitere Kenntnisse / Interessen</b>';
                    $this->element('addScript', ['script' => 
                        JS_NAMESPACE . ".Helper.addNewTagsToSelect2Multidropdown('select#users-skills-ids', ".json_encode($this->request->getSession()->read('newSkillsProfile')).");
                    "]);
                
                    echo $this->Form->control('Users.skills._ids', [
                        'multiple' => 'select',
                        'data-tags' => true,
                        'data-token-separators' => "[',']",
                        'label' => false,
                        'options' => $skillsForDropdown,
                    ]);
                echo '</div>';

                echo $this->Form->control('Users.about_me', ['label' => 'Über mich (max. 1.000 Zeichen)', 'type' => 'textarea', 'data-private' => true]).'<br />';

                if ($loggedUser->isAdmin()) {
                    echo $this->Form->control('Users.status', ['type' => 'select', 'options' => Configure::read('AppConfig.status2')]). '<br />';
                }

                echo $this->element('cancelAndSaveButton', ['hideCancelButton' => $isMyProfile]);

            echo $this->Form->end();
      ?>


    </div>


</div>

<?php if ($isMyProfile) { ?>
    <a style="float:right;clear:both;margin-top:5px;" class="button gray" href="/users/delete">Mein Profil löschen</a>
<?php } ?>