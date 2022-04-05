<?php
    echo $this->element('highlightNavi', ['main' => 'Reparaturwissen']);
    $this->element('addScript', ['script' =>
        JS_NAMESPACE.".Helper.initBoxToggle();".
        JS_NAMESPACE.".Helper.initSkillFilterForKnowledges();
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

    echo '<div id="rw-' . $knowledge->uid . '" href="#collapse' . $knowledge->uid . '" class="box-toggle' . $knowledge->uid . ' box-toggle knowledge-item ' . join(' ', $knowledge->itemSkillClasses) . '">';
        echo $knowledge->title;
    echo '</div>';

    echo '<div id="collapse' . $knowledge->uid . '" class="collapse ' . join(' ', $knowledge->itemSkillClasses) .'">';
        echo $knowledge->text;
    echo '</div>';

}

?>