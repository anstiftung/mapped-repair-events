<?php
use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
echo $this->element('highlightNavi', ['main' => 'Aktive']);
?>

<div class="top-wrapper">
    <?php echo $this->element('users/publicUser', ['user' => $user, 'headingTag' => 'h1', 'linkToProfile' => false]); ?>
</div>

<div class="dotted-line-full-width"></div>

<div class="bottom-wrapper">
    <?php if ($user->about_me != '' || !empty($user->skills)) { ?>
        <div class="about-me-wrapper">
            <b>Mein Profil</b>
            <div class="about-me-content">
                <?php
                    if ($user->about_me != '') {
                        echo '<p style="margin-bottom:10px;">' . StringComponent::prepareTextForHTML($user->about_me) . '</p>';
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
        <strong><?php echo Configure::read('AppConfig.categoriesNameUsers'); ?> </strong>
        <div class="sc"></div>

        <div id="skill_icons">
            <?php
                foreach($user->categories as $category) {
                    echo '<a href="' . $this->Html->urlUsers($category->name) . '" title="'.h($category->name).'" class="skill_icon small '.h($category->icon).'"></a>';
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
            $additionalContactPossibilities[] = $this->Html->link($user->website, $user->website, ['rel' =>'no-follow', 'target' => '_blank']);
        }
        if ($user->phone) {
            $additionalContactPossibilities[] = $user->phone;
        }
        $additionalSocial = [];
        if ($user->facebook_username) {
            $additionalSocial[] = $this->Html->link(
                '<i class="fab fa-facebook-square"></i>',
                $this->Html->getFacebookUrl($user->facebook_username),
                [
                    'rel' =>'no-follow',
                    'target' => '_blank',
                    'escape' => false,
                ]
            );
        }
        if ($user->twitter_username) {
            $additionalSocial[] = $this->Html->link(
                '<i class="fab fa-square-x-twitter"></i>',
                'https://twitter.com/' . $user->twitter_username,
                [
                    'rel' =>'no-follow',
                    'target' => '_blank',
                    'escape' => false,
                ]
            );
        }
        if ($user->feed_url) {
            $additionalSocial[] = $this->Html->link(
                '<i class="fas fa-square-rss"></i>',
                $user->feed_url,
                [
                    'rel' =>'no-follow',
                    'target' => '_blank',
                    'escape' => false,
                ]
            );
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
        echo '</strong><br /><br />';
    ?>
        <div class="workshop-link-wrapper">
            <?php foreach($user->workshops as $workshop) { ?>
                <a class="button gray workshop" href="<?php echo $this->Html->urlWorkshopDetail($workshop->url); ?>">
                    <?php echo $workshop->name; ?>
                </a>
            <?php } ?>
        </div>
    <?php } ?>

    <a style="clear:both;margin-top:10px;float:left;" class="button" href="<?php echo $this->Html->urlUsers(); ?>">Mehr Aktive anzeigen</a>
</div>