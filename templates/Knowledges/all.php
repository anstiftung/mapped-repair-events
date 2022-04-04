<?php
    use App\Controller\Component\StringComponent;

    echo $this->element('highlightNavi', ['main' => 'Reparaturwissen']);
    $this->element('addScript', ['script' => "
        MappedRepairEvents.Helper.initSkillFilter();
    "]);
?>

<h1></h1>

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

<?php

foreach($knowledges as $knowledge) {
    echo '<h2>' . $knowledge->title . '</h2>';
    echo $knowledge->text;
    echo '<br />';
}

?>