<?php

$this->element('addScript', ['script' => 
    JS_NAMESPACE.".Helper.bindApplyForCollaborationButtonUser();
"]);
echo $this->element('jqueryTabsWithoutAjax', [
        'links' => $this->Html->getUserBackendNaviLinks($appAuth->getUserUid(), true, $appAuth->isOrga())
    ]
);
?>

<div class="profile ui-tabs custom-ui-tabs ui-widget-content">
	<div class="ui-tabs-panel">
		<?php
          echo $this->element('apply', [
            'heading' => 'Aktive Mitgliedschaften',
            'currentRelationsText' => 'Du bist derzeit bei den folgenden Initiativen beteiligt',
            'applicationText' => $appAuth->isAdmin() ? 'Bitte wähle User und Initiative aus, für die du als Admin eine Zuordnung erstellen möchtest.' : 'Wähle eine Initiative aus, der du beitreten möchtest',
            'associatedWorkshops' => $associatedWorkshops,
            'relationModel' => 'users_workshops',
            'relationType' => 'Users',
            'type' => 'user',
            'buttonText' => 'Initiative beitreten',
            'deleteTitle' => 'Austreten',
            'explainationText' => $appAuth->isAdmin() ? 'Die Zuordnung wird bereits bestätigt sein.' : 'Klicke dann auf "Initiative beitreten". Die ausgewählte Initiative erhält deine Beitrittsanfrage zum Bestätigen.',
          ]);
        ?>
	</div>
</div>
