<?php
    use App\Controller\Component\StringComponent;

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

        echo '<div>' . $knowledge->text . '</div>';

        if(!empty($knowledge->categories)) {
            echo '<div id="skill_icons">';
                foreach($knowledge->categories as $category) {
                    echo '<a href="javascript:void(0);" title="'.h($category->name).'" class="skill_icon small '.h($category->icon).'"></a>';
                }
            echo '</div>';
            echo '<div class="sc"></div>';
        }

        if (!empty($knowledge->skills)) {
            foreach($knowledge->skills as $skill) {
                echo '<a href="javascript:void(0);" title="'.h($skill->name).'" class="button">'.h($skill->name).'</a>';
            }
            echo '<div class="sc"></div>';
        }

    echo '</div>';

}

?>