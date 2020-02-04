<?php
use Cake\ORM\TableRegistry;
?>

<h1><?php echo $first; ?></h1>

<?php

  $isLoggedUserApproved = false;
  if (isset($context) && $context['className'] == 'Workshop' && $appAuth->user()) {
    $workshopTable = TableRegistry::get('Workshops');
    $isLoggedUserApproved = $workshopTable->isLoggedUserApproved($context['object']->uid, $appAuth->getUserUid());
  }
  if (isset($context) && $appAuth->user() &&
    ($appAuth->isAdmin() && ($context['className'] != 'Workshop')
    )
  ) {
      $editMethod = 'url' . $context['className'] . 'Edit';
      echo '<a title="bearbeiten" id="object-edit-icon" href="' . $this->Html->$editMethod($context['object']->uid).'">';
      echo '<i class="far fa-edit fa-border"></i>';
    echo '</a>';
  }
  
?>
