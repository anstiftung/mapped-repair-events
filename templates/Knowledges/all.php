<?php
    echo $this->element('highlightNavi', ['main' => 'Reparaturwissen']);
    $this->element('addScript', ['script' => "
        MappedRepairEvents.Helper.initSkillFilterForKnowledges();
    "]);
?>

<h1><?php echo $metaTags['title']; ?></h1>

<?php
    echo '<div class="skills-wrapper">';
        echo '<label>Suche nach bestimmtem Schlagwort</label>';
        echo $this->Form->control('skills', [
            'type' => 'select',
            'label' => false,
            'empty' => 'Alle anzeigen',
            'options' => $skillsForDropdown,
        ]);
    echo '</div>';
?>
<div class="sc"></div>

<?php

foreach($knowledges as $knowledge) {
    echo '<div id="rw-' . $knowledge->uid . '" class="knowledge-item ' . join(' ', $knowledge->itemSkillClasses) . '">';
        echo '<h2>' . $knowledge->title . '</h2>';
        echo $knowledge->text;
    echo '</div>';
}

?>