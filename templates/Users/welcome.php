<?php
declare(strict_types=1);
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
                'links' => $this->Html->getUserBackendNaviLinks($loggedUser->uid, true, $loggedUser->isOrga())
            ]
        );
    ?>

    <div class="ui-tabs custom-ui-tabs ui-widget-content">
        <div class="ui-tabs-panel">
            <div id="profile">
                <?php
                    echo $this->Html->getUserProfileImage($loggedUser);
                ?>
                <div class="sc"></div>
                <?php echo '<span class="greenbold">'.$loggedUser->firstname.' '.$loggedUser->lastname.'</span><span> ( '.$loggedUser->nick.' )</span>'; ?>
                <?php echo '<div>'.$loggedUser->email.' ( ID: '.$loggedUser->uid.' )</div>'; ?>
                <div style="height:8px;"></div>
                <?php echo '<div>'.$loggedUser['street'].'</div>'; ?>
                <?php echo '<div>'.$loggedUser['zip']. ' '. $loggedUser['city'].' / '. $loggedUser['country_code'].'</div>'; ?>

                <br /><br />
            </div>

            <div class="left-wrapper">

                <?php echo $this->element('heading', ['first' => __('User Welcome Dashboard Headline') ]); ?>
                <div><?php echo __('User Welcome Dashboard Introtext');?> </div>

                <br />
                <p>
                <?php echo $homepageIntrotext?->text; ?>
                </p>

                <div class="sc"></div>

            </div>
        </div>
    </div>

</div>