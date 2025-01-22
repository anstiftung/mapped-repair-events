<?php
declare(strict_types=1);
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

            <?php echo $this->Html->Tag('h1', 'Organisator*in'); ?>
            <div class="description">
                <br />
                <br />
                <p>
                    <?php echo Configure::read('AppConfig.registerAsOrgaInfoText'); ?>
                    <br /><br /><br /><br /><strong>Registriere dich als Organisator*in und</strong>
                </p>
                <br />
                <ul>
                    <li>trage deine Initiative ein</li>
                    <li>verwalte Termine deiner Initiative</li>
                    <li>sei Kontaktperson für deine Initiative</li>
                </ul>

                <a class="button registration-button" href="<?php echo $this->request->getSession()->read('isMobile') ? 'javascript:void(0);' : '#reg'?>"
                    title="<?php echo __('Intro:Take part as {0}', ['Organisator*in']) ?>"><?php echo __('Intro:Take part as {0}', ['Organisator*in']) ?></a>
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

            <?php echo $this->Html->Tag('h1', Configure::read('AppConfig.repairHelperName')); ?>
            <div class="description">
                <br />
                <br />
                <?php echo Configure::read('AppConfig.registerAsRepairHelperInfoText'); ?>
                <a class="button registration-button" href="<?php echo $this->request->getSession()->read('isMobile') ? 'javascript:void(0);' : '#reg'?>"
                    title="<?php echo __('Intro:Take part as {0}', [Configure::read('AppConfig.repairHelperName')]) ?>"><?php echo __('Intro:Take part as {0}', [Configure::read('AppConfig.repairHelperName')]) ?></a>
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

    <?php
        echo $this->element('newsletter/newsletterButton');
    ?>

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