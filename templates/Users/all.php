<?php
    echo $this->element('highlightNavi', ['main' => 'Aktive']);
    $this->element('addScript', ['script' => "
        MappedRepairEvents.Helper.initSkillFilter();
    "]);
?>

<h1>
    <?php if (isset($skill)) { ?>
    	<a class="button" href="javascript:void(0);"><?php echo $skill->name; ?></a> (<?php echo $users->count(); ?> Aktive)</h1>
    <?php } else { ?>
    	<?php echo $metaTags['title']; ?> (<?php echo $users->count(); ?>)
    <?php } ?>
</h1>

<?php
    echo '<div class="skills-wrapper">';
        echo '<label>Suche nach bestimmtem Schlagwort</label>';
        echo $this->Form->control('skills', [
            'type' => 'select',
            'label' => false,
            'empty' => 'Alle anzeigen',
            'options' => $skillsForDropdown,
            'value' => isset($skill) ? $skill->id : ''
        ]);
    echo '</div>';
?>
<div class="sc"></div>

<div class="dotted-line-full-width"></div>

<?php if (isset($skill)) {
    echo $this->element('users/userFilter', ['urlMethod' => 'urlSkillDetail', 'skill' => $skill]);
} else {
    echo $this->element('users/userFilter', ['urlMethod' => 'urlUsers']);
}
?>

<div class="dotted-line-full-width"></div>

<div class="page-wrapper">
    <?php
        foreach($users as $user) {
            echo $this->element('users/publicUser', ['user' => $user, 'headingTag' => 'h2', 'linkToProfile' => true]);
        }
    ?>
    <div class="sc"></div>
    <a class="button" style="float:left;" href="<?php echo $overviewLink; ?>">Zurück zur Übersicht</a>
</div>