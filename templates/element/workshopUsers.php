<?php

use Cake\Core\Configure;

if (count($workshop->{$objectMember}) > 0) {
 
 echo '<b class="heading">Die folgenden Personen sind bei dieser Initiative aktiv</b>';
 ?>
 
    <table class="list">
  
    <tr>
      <th><?php echo $typeSingularTranslated; ?></th>
      <th>Anfrage vom</th>
      <th>Bestätigt am</th>
      <th class="icon">E-Mail an UserIn</th>
      <th class="icon">UserIn ablehnen</th>
    </tr>

<?php
   
    foreach($workshop->{$objectMember} as $user) {
    
    echo '<tr>';

      echo '<td>';
        echo '<div class="hide userUid">'.$user->uid.'</div>';
        echo '<div class="hide workshopUid">'.$workshop->uid.'</div>';
        echo '<div class="hide user-type" class="hide">'.$type.'</div>';
        echo $user->name;
        
        $groupNames = [];
        $groupIds = [];
        if (isset($user->groups)) {
            foreach($user->groups as $group) {
                $groupNames[] = Configure::read('AppConfig.htmlHelper')->getUserGroupsForWorkshopDetail()[$group['id']];
                $groupIds[] = $group['id'];
            }
        }
        if (!empty($groupNames)) {
            echo ' <small>('.implode(', ', $groupNames).')</small>';
        }
        
        
      echo '</td>';
      
      echo '<td>';
        if ($user['_joinData']->created) {
            echo $user['_joinData']->created->i18nFormat(Configure::read('DateFormat.de.DateNTimeShort'));
        }
      echo '</td>';

      echo '<td>';
        if ($user['_joinData']->approved) {
            echo $user['_joinData']->approved->i18nFormat(Configure::read('DateFormat.de.DateNTimeShort'));
        } else {
            echo $this->Html->link(
              $this->request->getSession()->read('isMobile') ? 'Bestätigen' : 'Bestätige die Anfrage zur Mitarbeit bei deiner Initiative.',
              'javascript:void(0);',
              ['class' => 'button blink approve']
            );
        }
        
      echo '</td>';

      echo '<td class="icon">';
          echo $this->Html->link(
              '<i class="far fa-envelope fa-border"></i>'
            ,'mailto:'.$user['email']
            ,[
               'escape' => false
              ,'title' => 'E-Mail'
            ]
          );
      echo '</td>';

      echo '<td class="icon">';
      if (in_array($workshop->uid, $workshopsWhereUserIsLastOrgaUserUids) && in_array(GROUPS_ORGA, $groupIds) && !is_null($user['_joinData']->approved)) {
              $deleteClass = 'refuse-not-possible';
              $deleteIcon = '<i class="fas fa-times fa-border"></i>';
              $deleteTitle = 'Ablehnen nicht möglich';
          } else {
              $deleteClass = 'refuse';
              $deleteIcon = '<i class="far fa-trash-alt fa-border"></i>';
              $deleteTitle = 'Ablehnen?';
          }
          echo $this->Html->link(
              $deleteIcon,
              'javascript:void(0);',
              [
                  'class' => $deleteClass,
                  'escape' => false,
                  'title' => $deleteTitle
              ]
          );
      echo '</td>';

     echo '</tr>';
     
   }
   
    echo '</table>';        
    }
?>