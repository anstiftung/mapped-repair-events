<?php
declare(strict_types=1);
use Cake\Utility\Inflector;
?>

    <div class="sort">
      Sortieren nach:
      <?php
        foreach($sortableFields as $sortableField) {
          echo $paginator->sort($sortableField['field'], $sortableField['name']);
        }
       ?>
    </div>

    <table class="list">

      <tr>
        <th class="alphabet"></th>
        <?php
          foreach($columns as $column) {
            echo '<th>'.$column.'</th>';
          }
        ?>
      </tr>

  <?php
  foreach($objects as $object) {

    $useExtraRow = false;

    $o = $object;
    if ($object->user) {
        $o = $object->user;
    }

    $stringForStartingLetter = $o->name;

    if(in_array($this->request->query('sort'), ['Users.firstname'])) {
        $stringForStartingLetter = $o->firstname;
    }
    if(in_array($this->request->query('sort'), ['Users.lastname'])) {
        $stringForStartingLetter = $o->lastname;
    }
    if(in_array($this->request->query('sort'), ['Workshops.city', 'Users.city'])) {
      $stringForStartingLetter = $o->city;
      $useExtraRow = true;
    }
    if(in_array($this->request->query('sort'), ['Countries.name_de'])) {
      $stringForStartingLetter = $o->country->name_de;
      $useExtraRow = true;
    }

    $startingLetter = ucfirst(substr($stringForStartingLetter, 0, 1));
    $isNewStartingLetter = !isset($oldStartingLetter) || $startingLetter != $oldStartingLetter;

    $isNewStringForStartingLetter = !isset($oldStringForStartingLetter) || $stringForStartingLetter != $oldStringForStartingLetter;

    $alphabetString = '<td class="alphabet">'.$startingLetter.'</td>';
    $noAlphabetString = '<td class="no-alphabet"></td>';

    if ($isNewStartingLetter) {
      $alphabet = $alphabetString;
    } else {
      $alphabet = $noAlphabetString;
    }

    if ($useExtraRow && $isNewStartingLetter) {
      echo '<tr>';
        echo $alphabet;
        echo '<td colspan="2"><b>'.$stringForStartingLetter.'</b></td>';
      echo '</tr>';
    }

    if ($useExtraRow && $isNewStringForStartingLetter && !$isNewStartingLetter) {
      echo '<tr>';
        echo $alphabet;
        echo '<td colspan="2"><b>'.$stringForStartingLetter.'</b></td>';
      echo '</tr>';
    }

    echo '<tr class="content-row uid-'.$object->uid.'">';

        $alphabet = $noAlphabetString;
        if (!$useExtraRow && $isNewStartingLetter) {
          $alphabet = $alphabetString;
        }
        echo $alphabet;

        echo '<td>';

          $detailUrl = 'url'.$objectClass.'Detail';
          echo '<b>'.$this->Html->link($o->name, $this->Html->$detailUrl($object->url)).'</b>';

          if ($object->image != '') {
            echo '<br /><img class="object-image" src="'.$this->Html->getThumbs50Image($object->image, strtolower(Inflector::pluralize($objectClass))).'" />';
          }

        echo '</td>';

        echo '<td>';

          if ($o->street != '') {
            echo $o->street . ', ';
          }

          echo $o->zip .' <b>' . $o->city.'</b>, ';
          if (!empty($o->country->name_de)) {
              echo $o->country->name_de;
          }
          if ($o->email != '') {
            echo '<br />'.$this->Html->link($o->email, 'mailto:'.$o->email);
          }

          if ($o->website != '') {
            echo '<br />'.$this->Html->link($o->website);
          }

      echo '</td>';

    echo '</tr>';

    $oldStartingLetter = $startingLetter;
    $oldStringForStartingLetter = $stringForStartingLetter;

  }
?>

</table>