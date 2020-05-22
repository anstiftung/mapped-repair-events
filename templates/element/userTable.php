<?php

use Cake\Core\Configure;
use Cake\Utility\Inflector;

if (empty($extraFields)) $extraFields = [];
$urlObjectEdit = 'url'.Inflector::singularize($className).'Edit';
$urlObjectDelete = 'url'.Inflector::singularize($className).'Delete';
$urlObjectDetail = 'url'.Inflector::singularize($className).'Detail';

echo '<br />';
if ($object->status == APP_ON) {
    echo $this->Html->link(
        '<i class="fas fa-link"></i> ' . $object->name,
        $this->Html->urlWorkshopDetail($object->url),
        ['escape' => false, 'class' => 'icon-link-text']
    );
} else {
    echo '<h2>'.$object->name.'</h2>';
}

?>

    <table class="list">

      <tr>
        <?php
        foreach($extraFields as $extraField) {
          echo '<th>'.$extraField['heading'].'</th>';
        }
        ?>
        <th>Id</th>
        <th>Status</th>
        <th>Erstellt am</th>
        <th>Zuletzt bearbeitet</th>
        <th class="icon">löschen</th>
        <th class="icon">bearbeiten</th>
        <th class="icon">anzeigen</th>
      </tr>

  <?php

    echo '<tr>';

      foreach($extraFields as $extraFieldFieldName => $extraField) {
        echo '<td>'.$object[$extraFieldFieldName].'</td>';
      }

      echo '<td>';
          echo $object->uid;
      echo '</td>';

      echo '<td>';
        echo '<div class="hide workshopUid">'.$object->uid.'</div>';
        $status = Configure::read('AppConfig.status');
        echo '<strong class="'.($object->status == APP_ON ? 'green' : '').'">'.$status[$object->status].'</strong>';
      echo '</td>';

      echo '<td>';
        echo $object->created->i18nFormat(Configure::read('DateFormat.de.DateNTimeShort'));
      echo '</td>';

      echo '<td>';
        echo $object->updated->i18nFormat(Configure::read('DateFormat.de.DateNTimeShort'));
      echo '</td>';

      echo '<td class="icon">';
          echo $this->Html->link(
              '<i class="far fa-trash-alt fa-border"></i>',
              'javascript:void(0);',
              [
                  'title' => $objectNameDe . ' löschen',
                  'escape' => false,
                  'class' => 'delete-workshop'
              ]
          );
      echo '</td>';

      echo '<td class="icon">';
        echo $this->Html->link(
            '<i class="far fa-edit fa-border"></i>'
            ,$this->Html->$urlObjectEdit($object->uid)
            ,['title' => $objectNameDe . ' bearbeiten', 'escape' => false]
          );
       echo '</td>';

      echo '<td class="icon">';

        $preview = false;
        if ($object->status == APP_ON) {
          $icon =  '<i class="fas fa-arrow-right fa-border"></i>';
          $previewText = $objectNameDe . ' anzeigen';
        } else {
          $icon =  '<i class="fas fa-search fa-border"></i>';
          $previewText = $objectNameDe . ' als Vorschau anzeigen';
          $preview = true;
        }

        echo $this->Html->link(
           $icon
          ,$this->Html->$urlObjectDetail($object->url, $preview)
          ,['title' => $previewText, 'escape' => false]
        );
        echo '</td>';

     echo '</tr>';

    echo '</table>';

    echo '<hr />';

