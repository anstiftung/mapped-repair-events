<?php

if (!empty($fundinguploads)) {
    $i = 0;
    $uploadFormFields = '';
    foreach($fundinguploads as $fundingupload) {
        $label = $this->Html->link('Vorschau', $this->Html->urlFundinguploadDetail($fundingupload->id), ['target' => '_blank']);
        $uploadFormFields .= $this->Form->control('Fundings.fundinguploads_' . $uploadType . '.' . $i.'.id', ['type' => 'hidden']);
        $uploadFormFields .= $this->Form->control('Fundings.fundinguploads_' . $uploadType . '.' . $i.'.filename', ['label' => $label, 'escape' => false, 'readonly' => true]);
        $uploadFormFields .= $this->Form->control('Fundings.fundinguploads_' . $uploadType . '.' . $i.'.created', ['label' => 'Datum', 'readonly' => true]);
        $i++;
    }
    echo $this->Form->fieldset(
        $uploadFormFields,
        [
            'legend' => $legend,
        ]
    );

}
