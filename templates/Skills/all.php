<?php
use App\Controller\Component\StringComponent;
echo $this->element('highlightNavi', ['main' => 'Wissen & Können']);
?>

<h1>Alle Kenntnisse &amp; Interessen (<?php echo $this->Number->precision($skillCount, 0); ?>)</h1>

<div class="dotted-line-full-width"></div>

<?php
foreach($skills as $letter => $letterSkills) {

    echo '<div class="letter">' . $letter . '</div>';
    echo '<div class="skills">';
    $i = 0;
        foreach($letterSkills as $skill) {
            $count = count($skill->users);
            if ($count == 0) {
                continue;
            }
            switch($count) {
                case ($count > 16):
                    $class = 'skill-50';
                    break;
                case ($count > 8):
                    $class = 'skill-40';
                    break;
                case ($count > 4):
                    $class = 'skill-30';
                    break;
                case ($count > 2):
                    $class = 'skill-20';
                    break;
                case ($count > 0):
                    $class = 'skill-10';
                    break;
            }
            echo '<a href="'.$this->Html->urlSkillDetail($skill->id, StringComponent::slugify($skill->name)).'" class="'.$class.'">' . $skill->name . '</a>';
        }
    echo '</div>';

}

?>