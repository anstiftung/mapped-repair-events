<?php
declare(strict_types=1);
use Cake\ORM\TableRegistry;
?>

<h1><?php echo $first; ?></h1>

<?php

  $isLoggedUserApproved = false;
  if (isset($context) && $context['className'] == 'Workshop' && !empty($loggedUser)) {
      $workshopTable = TableRegistry::getTableLocator()->get('Workshops');
      $isLoggedUserApproved = $workshopTable->isLoggedUserApproved($context['object']->uid, $loggedUser->uid);
  }
  if (isset($context) && !empty($loggedUser) &&
    ($loggedUser->isAdmin() && ($context['className'] != 'Workshop')
    )
  ) {
      $editMethod = 'url' . $context['className'] . 'Edit';
      $hasSeries = $context['className'] === 'Event' && !empty($context['object']->series_uid);
      echo $this->Html->link('<i class="far fa-edit fa-border"></i>', $this->Html->$editMethod($context['object']->uid), [
          'title' => 'bearbeiten',
          'id' => 'object-edit-icon',
          'escape' => false,
      ] + ($hasSeries ? ['data-edit-event-series' => '1'] : []));
  }

?>
