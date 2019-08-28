<?php
    echo $this->element('highlightNavi', ['main' => 'Wissen & Können']);
?>

<h1><a class="button" href="javascript:void(0);"><?php echo $skill->name; ?></a> (<?php echo count($skill->users); ?> Aktive)</h1>

<?php
    echo $this->element('users/userFilter', ['urlMethod' => 'urlSkillDetail', 'skill' => $skill]);
?>

<div class="page-wrapper">
    <?php
        foreach($skill->users as $user) {
            echo $this->element('users/publicUser', ['user' => $user, 'headingTag' => 'h2', 'linkToProfile' => true]);
        }
    ?>
</div>