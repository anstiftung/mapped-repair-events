<?php
declare(strict_types=1);
use Cake\Datasource\FactoryLocator;
?>

<h1><?php echo $first; ?></h1>

<?php

  $isLoggedUserApproved = false;
  if (isset($context) && $context['className'] == 'Workshop' && !empty($loggedUser)) {
      $workshopTable = FactoryLocator::get('Table')->get('Workshops');
    $isLoggedUserApproved = $workshopTable->isLoggedUserApproved($context['object']->uid, $loggedUser->uid);
  }
  if (isset($context) && !empty($loggedUser) &&
    ($loggedUser->isAdmin() && ($context['className'] != 'Workshop')
    )
  ) {
      $editMethod = 'url' . $context['className'] . 'Edit';
      echo '<a title="bearbeiten" id="object-edit-icon" href="' . $this->Html->$editMethod($context['object']->uid).'">';
      echo '<i class="far fa-edit fa-border"></i>';
    echo '</a>';
  }

?>
