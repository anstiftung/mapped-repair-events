<?php

use Cake\Core\Configure;

$uploadFormFields = '<p>Noch keine Dateien vorhanden</p>';
if (!empty($fundinguploads)) {
    $uploadFormFields = '';
    $i = 0;
    foreach($fundinguploads as $fundingupload) {
        $label = $this->Html->link('Vorschau', $this->Html->urlFundinguploadDetail($fundingupload->id), ['target' => '_blank']);
        $formattedCreated = $fundingupload->created->i18nFormat(Configure::read('DateFormat.de.DateNTimeShort'));
        $uploadFormFields .= $this->Form->hidden('Fundings.fundinguploads_' . $uploadType . '.' . $i.'.id');
        $uploadFormFields .= $this->Form->control('Fundings.fundinguploads_' . $uploadType . '.' . $i.'.filename', ['label' => $label . ' / ' . $formattedCreated, 'escape' => false, 'readonly' => true]);
        $i++;
    }
}
echo $this->Form->fieldset(
    $uploadFormFields,
    [
        'legend' => $legend,
    ]
);

