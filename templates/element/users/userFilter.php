<?php
declare(strict_types=1);
?>

<div class="zip-wrapper">
    <div class="zip">
        Filtern nach:
        <?php
           for($i=9;$i>=0;$i--)  {
               $class = ['zip'];
               if ($this->request->getQuery('zip') > -1 && $this->request->getQuery('zip') == $i) {
                   $class[] = 'active';
               }
               if ($urlMethod == 'urlUsers') {
                   $url = $this->Html->urlUsers($filteredCategoryName, $i);
                   $unfilteredUrl = $this->Html->urlUsers($filteredCategoryName);
               } else {
                   $url = $this->Html->urlSkillDetail($skill->id, $skill->name, $i);
                   $unfilteredUrl = $this->Html->urlSkillDetail($skill->id, $skill->name);
               }
               echo '<a class="'.join(' ', $class).'" href="'.$url.'">PLZ ' . $i . ' ...</a> | ';
           }
        ?>
        <a href="<?php echo $unfilteredUrl; ?>">alle anzeigen</a>
    </div>
    <div class="sort">
      Sortieren nach:
          <?php
              echo $this->Paginator->sort('Users.created', 'Datum') . ' | ';
              echo $this->Paginator->sort('Users.nick', 'Nick');
        ?>
    </div>
</div>