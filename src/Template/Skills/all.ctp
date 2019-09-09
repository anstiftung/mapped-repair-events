<?php
use App\Controller\Component\StringComponent;
echo $this->element('highlightNavi', ['main' => 'Wissen & Können']);
?>

<h1>Alle Kenntnisse &amp; Interessen (<?php echo count($skills); ?>)</h1>

<div class="dotted-line-full-width"></div>

<?php
foreach($skills as $letter => $letterSkills) {
    
    echo '<div class="letter">' . $letter . '</div>';
    echo '<div class="skills">';
        foreach($letterSkills as $skill) {
//             $count = count($skill->users);
            $count = rand(1, 60);
            switch($count) {
                case ($count > 50):
                    $class = 'skill-50';
                    break;
                case ($count > 40):
                    $class = 'skill-40';
                    break;
                case ($count > 30):
                    $class = 'skill-30';
                    break;
                case ($count > 20):
                    $class = 'skill-20';
                    break;
                default:
                    $class = 'skill-10';
                    break;
            }
            echo '<a href="'.$this->Html->urlSkillDetail($skill->id, StringComponent::slugify($skill->name)).'" class="'.$class.'" title="Schlagwort '.count($skill->users).'x vergeben">' . $skill->name . '</a>';
        }
    echo '</div>';
    
}

?>