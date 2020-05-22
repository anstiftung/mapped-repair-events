<?php
if ($this->request->getSession()->read('isMobile')) {
    $this->element('addScript', ['script' => "
        $('#profile').before($('.left-wrapper'));
    "]);
}
?>

<div id="userwelcome">

    <?php echo $this->element('highlightNavi' ,['main' => '']); ?>

    <?php
        echo $this->element('jqueryTabsWithoutAjax', [
                'links' => $this->Html->getUserBackendNaviLinks($appAuth->getUserUid(), true, $appAuth->isOrga())
            ]
        );
    ?>

    <div class="ui-tabs custom-ui-tabs ui-widget-content">
        <div class="ui-tabs-panel">
            <div id="profile">
                <?php
                    echo $this->Html->getUserProfileImage($appAuth->getUser());
                ?>
                <div class="sc"></div>
                <?php echo '<span class="greenbold">'.$appAuth->getUserFirstname().' '.$appAuth->getUserLastname().'</span><span> ( '.$appAuth->getUserNick().' )</span>'; ?>
                <?php echo '<div>'.$appAuth->getUserEmail().' ( ID: '.$appAuth->getUserUid().' )</div>'; ?>
                <div style="height:8px;"></div>
                <?php echo '<div>'.$appAuth->getUser()['street'].'</div>'; ?>
                <?php echo '<div>'.$appAuth->getUser()['zip']. ' '. $appAuth->getUser()['city'].' / '. $appAuth->getUser()['country_code'].'</div>'; ?>

                <br /><br />
            </div>

            <div class="left-wrapper">

                <?php echo $this->element('heading', ['first' => __('User Welcome Dashboard Headline') ]); ?>
                <div><?php echo __('User Welcome Dashboard Introtext');?> </div>

                <br />
                <p>
                <?php echo $homepageIntrotext->text; ?>
                </p>

                <div class="sc"></div>

            </div>
        </div>
    </div>

</div>