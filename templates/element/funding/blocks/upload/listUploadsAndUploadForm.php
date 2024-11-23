<?php

use App\Model\Entity\Fundingupload;

$uploadType = Fundingupload::TYPE_MAP[$uploadType];

$this->Form->unlockField('delete_fundinguploads_' . $uploadType);
$this->Form->unlockField('files_fundinguploads_' . $uploadType);

if (!empty($fundinguploads)) {

    echo '<p style="padding:10px;">Bereits hochgeladen</p>';

    $i = 0;
    foreach($fundinguploads as $fundingupload) {
        $label = $this->Html->link('Vorschau', $this->Html->urlFundinguploadDetail($fundingupload->id), ['target' => '_blank']);
        echo $this->Form->control('Fundings.fundinguploads_' . $uploadType . '.' . $i.'.id', ['type' => 'hidden']);
        echo $this->Form->control('Fundings.fundinguploads_' . $uploadType . '.' . $i.'.owner', ['type' => 'hidden']);
        echo $this->Form->control('Fundings.fundinguploads_' . $uploadType . '.' . $i.'.type', ['type' => 'hidden']);
        echo $this->Form->control('Fundings.fundinguploads_' . $uploadType . '.' . $i.'.filename', ['label' => $label, 'readonly' => true, 'class' => 'is-upload no-verify', 'escape' => false, 'data-upload-type' => $uploadType]);
        $i++;
    }
}

if (!$showUploadForm) {
    return;
}

$this->element('addScript', ['script' =>
    JS_NAMESPACE.".Funding.initBindDeleteFundinguploads();"
]);

echo '<div style="margin-top:10px;padding:10px;">';
    echo '<p>Nur PDF, JPG und PNG-Dateien sind erlaubt, und jede Datei muss unter 5 MB sein.</p>';
echo '</div>';

echo $this->Form->control('Fundings.files_fundinguploads_' . $uploadType . '[]', [
    'type' => 'file',
    'multiple' => $multiple,
    'label' => '',
    'accept' => '.jpg, .png, .pdf, .jpeg', 
]);

echo '<div class="upload-button-wrapper">';
    echo $this->Form->button('Dateien hochladen', [
        'type' => 'submit',
        'class' => 'upload-button rounded',
    ]);
echo '</div>';
