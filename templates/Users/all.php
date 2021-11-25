<?php
    use App\Controller\Component\StringComponent;

    echo $this->element('highlightNavi', ['main' => 'Aktive']);
    $this->element('addScript', ['script' => "
        MappedRepairEvents.Helper.initSkillFilter();
    "]);
?>

<h1>
    <?php if (isset($skill)) { ?>
        <a class="button" href="javascript:void(0);">
            <?php echo $skill->name; ?>
        </a> (<?php echo $this->Number->precision($users->count(), 0); ?> Aktive)
    <?php } else if (!is_null($filteredCategoryName)) { ?>
        <a class="button" href="javascript:void(0);">
            <?php echo '<img class="skill-icon" title="'.h($filteredCategoryName).'" src="/img/icons-skills/'.h($filteredCategoryIcon).'.png" /> '; ?>
            <span><?php echo $filteredCategoryName; ?></span>
        </a> (<?php echo $this->Number->precision($users->count(), 0); ?> Aktive)
    <?php } else { ?>
        <?php echo $metaTags['title']; ?> (<?php echo $this->Number->precision($users->count(), 0); ?>)
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
            'value' => is_null($filteredCategoryName) ? (isset($skill) ? $skill->id : '') : StringComponent::slugify($filteredCategoryName),
        ]);
    echo '</div>';
?>
<div class="sc"></div>

<div class="dotted-line-full-width"></div>

<?php if (isset($skill)) {
    echo $this->element('users/userFilter', ['urlMethod' => 'urlSkillDetail', 'skill' => $skill, 'filteredCategoryName' => null]);
} else {
    echo $this->element('users/userFilter', ['urlMethod' => 'urlUsers', 'filteredCategoryName' => $filteredCategoryName]);
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