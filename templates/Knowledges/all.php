<?php
    use App\Controller\Component\StringComponent;

echo $this->element('highlightNavi', ['main' => 'Reparaturwissen']);
    $this->element('addScript', ['script' =>
        JS_NAMESPACE.".Helper.initBoxToggle();".
        JS_NAMESPACE.".Helper.initSkillFilterForKnowledges();".
        JS_NAMESPACE.".Helper.bindKnowledgeFilterButtons();
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

    echo '<div class="knowledge-row '. join(' ', $knowledge->itemSkillClasses) .'">';

        echo '<div id="rw-' . $knowledge->uid . '" href="#collapse' . $knowledge->uid . '" class="box-toggle' . $knowledge->uid . ' box-toggle knowledge-item">';
            echo $knowledge->title;
            if ($appAuth->isAdmin()) {
                echo '<a class="knowledge-edit-icon" href="' . $this->Html->urlKnowledgeEdit($knowledge->uid).'">';
                echo '<i class="far fa-edit fa-border"></i>';
                echo '</a>';
            }
        echo '</div>';

        echo '<div id="collapse' . $knowledge->uid . '" class="collapse">';

            echo '<div class="text-wrapper">' . $knowledge->text . '</div>';

            if(!empty($knowledge->categories)) {
                echo '<div id="skill_icons">';
                    foreach($knowledge->categories as $category) {
                        echo '<a href="javascript:void(0);" data-val="'.h(StringComponent::slugify($category->name)).'" title="Filtern nach '.h($category->name).'" class="skill_icon small '.h($category->icon).'"></a>';
                    }
                echo '</div>';
                echo '<div class="sc"></div>';
            }

            if (!empty($knowledge->skills)) {
                foreach($knowledge->skills as $skill) {
                    echo '<a href="javascript:void(0);" data-val="'.$skill->id.'" title="Filtern nach '.h($skill->name).'" class="button">'.h($skill->name).'</a>';
                }
                echo '<div class="sc"></div>';
            }

        echo '</div>';

    echo '</div>';

}

?>