<?php

use Cake\Core\Configure;

$this->element('addScript', ['script' => 
    JS_NAMESPACE.".Helper.bindWorkshopUserActions();
"]);
?>

<?php echo $this->element('heading', ['first' => $heading]); ?>

<?php if (count($associatedWorkshops->toArray()) > 0) { ?>

<h2><?php echo $currentRelationsText; ?></h2>

    
    <table class="list">
      
      <tr>
        <th>Name der Initiative</th>
        <th>Anfrage vom</th>
        <th>Bestätigt am</th>
        <th class="icon">Austreten</th>
        <th class="icon">E-Mail</th>
        <?php if ($type == 'user') { ?>
          <th class="icon">Bearbeiten</th>
        <?php } ?>
        <th class="icon">Link</th>
      </tr>

<?php foreach($associatedWorkshops as $associatedWorkshop) {
    
    foreach($associatedWorkshop->users as $user) {
        if ($user->uid == $appAuth->getUserUid()) {
            $userEntity = $user;
        }
    }
    
    echo '<tr>';

      echo '<td>';
        if ($type == 'user') {
          $userUid = $appAuth->getUserUid();
        }
        echo '<div class="hide userUid">'.$userUid.'</div>';
        echo '<div class="hide workshopUid">'.$associatedWorkshop->uid.'</div>';
        echo '<div class="hide user-type">'.$type.'</div>';
        echo $associatedWorkshop->name;
      echo '</td>';

      echo '<td>';
        echo $userEntity->_joinData->created->i18nFormat(Configure::read('DateFormat.de.DateNTimeShort'));
      echo '</td>';

      echo '<td>';
      if ($userEntity->_joinData->approved) {
          echo $userEntity->_joinData->approved->i18nFormat(Configure::read('DateFormat.de.DateNTimeShort'));
        } else {
          echo '<b>noch nicht bestätigt</b>';
        }
      echo '</td>';
      
      echo '<td class="icon">';
      if (in_array($associatedWorkshop->uid, $workshopsWhereUserIsLastOrgaUserUids) && $appAuth->isOrga() && !is_null($userEntity->_joinData->approved)) {
              $deleteClass = 'resign-not-possible';
              $deleteIcon = '<i class="fas fa-times fa-border"></i>';
              $deleteTitle = 'Austreten nicht möglich';
          } else {
              $deleteClass = 'resign';
              $deleteIcon = '<i class="far fa-trash-alt fa-border"></i>';
              $deleteTitle = 'Austreten?';
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
      
      echo '<td class="icon">';
          echo $this->Html->link(
              '<i class="far fa-envelope fa-border"></i>',
            'mailto:'.$associatedWorkshop->email,
            [
              'escape' => false,
              'title' => 'E-Mail'
            ]
          );
      echo '</td>';
      

      if ($type == 'user') {
          echo '<td class="icon">';
            if ($userEntity->_joinData->approved) {
                echo $this->Html->link(
                    '<i class="far fa-edit fa-border"></i>',
                    $this->Html->urlWorkshopEdit($associatedWorkshop->uid),
                    [
                        'title' => Configure::read('AppConfig.initiativeNameSingular') . ' bearbeiten',
                        'escape' => false
                    ]
                  );
            } else {
                echo $this->Html->link(
                    '<i class="far fa-edit fa-border"></i>',
                    'javascript:alert(\'Mitgliedschaft ist noch nicht bestätigt.\');',
                    [
                        'title' => Configure::read('AppConfig.initiativeNameSingular') . ' bearbeiten',
                        'escape' => false,
                        'disabled' => 'disabled',
                        'class' => 'disabled'
                    ]
                );
            }
           echo '</td>';
      }
      
      echo '<td class="icon">';
                
        $preview = false;
        if ($associatedWorkshop->status == APP_ON ) {
          $icon =  '<i class="fas fa-arrow-right fa-border"></i>';
          $previewText = 'Initiative anzeigen';
        } else {
          $icon =  '<i class="far fa-search fa-border"></i>';
          $previewText = 'Initiative als Vorschau anzeigen';
          $preview = true;
        }
        
        echo $this->Html->link(
          $icon
          ,$this->Html->urlWorkshopDetail($associatedWorkshop->url, $preview)
          ,['title' => $previewText, 'escape' => false]
        );
        echo '</td>';      
    
     echo '</tr>';
     
  }
  echo '</table>';
  
}
?>

<?php if (!empty($workshopsForDropdown)) { ?>

<h2 style="margin-top: 20px;"><?php echo $applicationText; ?></h2>
<p><?php echo $explainationText; ?></p>

<form id="workshopApply" action="<?php echo $this->request->getAttribute('here'); ?>" method="post">

    <?php
    if ($appAuth->isAdmin()) {
        echo '<div style="margin-right:10px;float: left;">';
            echo $this->Form->control($relationModel.'.user_uid', [
                'type' => 'select',
                'options' => $usersForDropdown,
                'label' => '',
                'style' => 'float: left;'
            ]);
        echo '</div>';
    }
    echo $this->Form->control($relationModel.'.workshop_uid', [
        'type' => 'select',
        'options' => $workshopsForDropdown,
        'label' => ''
    ]);
    ?>
    <button id="mitarbeits-anfrage-stellen" type="button" class="rounded" style="margin-left: 10px;">
      <?php echo $buttonText; ?>
    </button>

</form>
    
<?php } ?>