<?php
use Cake\Core\Configure;
$this->element('addScript', ['script' =>
  JS_NAMESPACE.".Helper.bindWorkshopUserActions('".Configure::read('AppConfig.notificationMailAddress')."');
"]);

echo $this->element('jqueryTabsWithoutAjax', [
    'links' => $this->Html->getUserBackendNaviLinks($loggedUser->uid, true, $loggedUser->isOrga())
]);
?>

<div class="profile ui-tabs custom-ui-tabs ui-widget-content">
    <div class="ui-tabs-panel">
        <?php echo $this->element('heading', ['first' => $metaTags['title']]); ?>

        <a href="<?php echo Configure::read('AppConfig.htmlHelper')->urlWorkshopNew(); ?>" class="button add-workshop">Neue Initiative erstellen</a>

        <p><br />Erstelle und verwalte deine Initiativen</p>

        <?php
        $i = 0;
        foreach($workshops as $workshop) {

          echo $this->element('userTable', [
             'object' => $workshop,
             'objectNameDe' => 'Initiative',
             'className' => 'Workshops',
            ]
          );

          echo $this->element('workshopUsers', [
             'relationType' => 'Users',
             'objectMember' => 'users',
             'type' => 'user',
             'typePluralTranslated' => 'Mitglieder',
             'typeSingularTranslated' => 'Mitglied',
             'workshop' => $workshop
            ]
          );

          $i++;
          if ($i < $workshops->count()) {
              echo '<div class="dotted-line"></div>';
          }

        }
        ?>

    </div>
</div>