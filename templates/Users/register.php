<?php
    use Cake\Core\Configure;
?>

<div id="UserRegIntroWrapper">

<?php
echo '<div align="center">' . $this->element('heading', [
        'first' => __('IntroScreen: Welcome - Choose your role and take part')
    ]) . '</div>';
?>

    <br />

    <div class="user-role-wrapper">

        <div class="half">
            <div class="iconhalf icon_orga"></div>
            <br />
            <br />

            <?php echo $this->Html->Tag('h1', 'OrganisatorIn'); ?>
            <div class="description">
                <br />
                <br />
                <p>
                    Du planst oder organisierst bereits eine <?php echo Configure::read('AppConfig.initiativeNameSingular'); ?>?<br /> Du
                    bist AnsprechpartnerIn und koordinierst die Mitwirkenden?<br />
                    <br /><br /><br /><strong>Registriere dich als OrganisatorIn und</strong>
                </p>
                <br />
                <ul>
                    <li>trage deine Initiative ein</li>
                    <li>verwalte Termine deiner Initiative</li>
                    <li>sei Kontaktperson für deine Initiative</li>
                </ul>

                <a class="button registration-button" href="<?php echo $this->request->getSession()->read('isMobile') ? 'javascript:void(0);' : '#reg'?>"
                    title="<?php echo __('Intro:Take part as Orga') ?>"><?php echo __('Intro:Take part as Orga') ?></a>
                <div class="sc"></div>
                <br />
            </div>

            <div class="fcph fcph-<?php echo GROUPS_ORGA; ?>">
                <?php echo $this->element('registrationForm', [
                    'url' => $this->Html->urlRegisterOrga(),
                    'userGroup' => GROUPS_ORGA,
                    'user' => $user
                ]); ?>
            </div>

        </div>

        <div class="half">

            <div class="iconhalf icon-repair"></div>
            <br />
            <br />

            <?php echo $this->Html->Tag('h1', 'ReparaturhelferIn'); ?>
            <div class="description">
                <br />
                <br />
                <p>
                    Du bist handwerklich geschickt und möchtest dein Wissen weitergeben?<br />
                    Du bist bereits als als ReparateurIn in einer Initiative aktiv?<br />
                    Du unterstützt ein Repair-Café beim Empfang, Cafébetrieb oder auf anderem Wege?<br />
                    <br /> <strong>Registriere dich als ReparaturhelferIn und</strong>
                </p>
                <br />
                <ul>
                    <li>gib Kenntnisse und Spezialgebiete an</li>
                    <li>tritt <?php echo Configure::read('AppConfig.platformName'); ?> bei</li>
                    <li>teile dein Wissen</li>
                </ul>

                <a class="button registration-button" href="<?php echo $this->request->getSession()->read('isMobile') ? 'javascript:void(0);' : '#reg'?>"
                    title="<?php echo __('Intro:Take part as Repair') ?>"><?php echo __('Intro:Take part as Repair') ?></a>
                <div class="sc"></div>
                <br />
            </div>

            <div class="fcph fcph-<?php echo GROUPS_REPAIRHELPER; ?>">
                <?php echo $this->element('registrationForm', [
                    'url' => $this->Html->urlRegisterRepairhelper(),
                    'userGroup' => GROUPS_REPAIRHELPER,
                    'user' => $user
                ]); ?>
            </div>


        </div>

    </div>

    <br />
    <br />
    <div class="sc"></div>

    <div class="newsletter-button">
        <a href="/newsletter" class="button mobile-full-width"><?php echo __('Intro: Not sure yet, Inform me via email if there is something new.'); ?></a>
    </div>
    <br />
    <br />
</div>
<a name="reg"></a>

<?php

$this->element('addScript', ['script' =>
    JS_NAMESPACE.".Helper.initRegistration();
"]);
if ($this->request->getSession()->read('isMobile')) {
    $this->element('addScript', ['script' =>
        JS_NAMESPACE.".MobileFrontend.initRegistration();
    "]);
}

?>