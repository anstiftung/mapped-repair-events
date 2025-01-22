<?php
declare(strict_types=1);
use App\Controller\Component\StringComponent;
use Cake\Core\Configure;

echo $this->element('highlightNavi', ['main' => 'Reparaturwissen']);
    $this->element('addScript', ['script' =>
        JS_NAMESPACE.".Helper.initBoxToggle();".
        JS_NAMESPACE.".Helper.initSkillFilterForKnowledges();".
        JS_NAMESPACE.".Helper.initCopyPermalinkToClipboard();".
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

<div class="text-wrapper">
    <p>
        An dieser Stelle entsteht eine Sammlung aus Reparaturwissen von Aktiven aus dem <?php echo Configure::read('AppConfig.titleSuffix'); ?>. Vorschläge für Ergänzungen etc. sind willkommen - wendet euch an <?php echo Configure::read('AppConfig.notificationMailAddress'); ?>.
    </p>

    <p>
        Die Urheberrechte der einzelnen Beiträge liegen bei den Verfasser*innen. Bei inhaltlichen Rückfragen und Fragen zur weiteren Nutzung bitte direkt an die jeweiligen Verfasser*innen wenden.
        Alle Webinaraufzeichnungen gesammelt gibt es im <a href="https://www.youtube.com/playlist?list=PL9rt2M0wvLVZLNe8O-nxNwSkZb3OIlXh8" target="_blank">YouTube-Kanal der anstiftung</a>.
    </p>
</div>


<?php
foreach($knowledges as $knowledge) {

    echo '<div class="knowledge-row '. join(' ', $knowledge->itemSkillClasses) .'">';

        echo '<div id="' . $knowledge->uid . '" href="#collapse' . $knowledge->uid . '" class="box-toggle' . $knowledge->uid . ' box-toggle knowledge-item">';

            echo $knowledge->title;

            if ($loggedUser?->isAdmin()) {
                echo $this->Html->link(
                    '<i class="far fa-edit fa-border"></i>',
                    $this->Html->urlKnowledgeEdit($knowledge->uid),
                    [
                        'class' => 'knowledge-edit-icon',
                        'escape' => false,
                    ]
                );
            }

        echo '</div>';

        echo '<div id="collapse' . $knowledge->uid . '" class="collapse">';

            echo '<div class="text-wrapper">' . $knowledge->text . '</div>';

            if (!empty($knowledge->categories) || !empty($knowledge->skills)) {
                echo '<hr />';
            }

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
            ?>

        <?php
        echo '</div>';

    echo '</div>';

}

?>