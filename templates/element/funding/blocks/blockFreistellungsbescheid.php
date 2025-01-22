<?php
declare(strict_types=1);

use App\Model\Entity\Funding;
use App\Model\Entity\Fundingupload;

echo '<fieldset>';

    echo '<legend>Freistellungsbescheid</legend>';

    echo $this->element('funding/status/freistellungsbescheidStatus', ['funding' => $funding]);

    echo '<div style="margin-bottom:10px;padding:10px;">';
        echo '<p>Das Ausstellungsdatum des Bescheides muss lesbar sein. Bitte lade alle Seiten des vorläufigen/regulären Freistellungsbescheides der gemeinnützigen Trägerorganisation als ein PDF (unter 5MB) hoch.</p>';
    echo '</div>';

    echo $this->element('funding/blocks/upload/listUploadsAndUploadForm', [
        'uploadType' => Fundingupload::TYPE_MAP_STEP_1[Fundingupload::TYPE_FREISTELLUNGSBESCHEID],
        'fundinguploads' => $funding->fundinguploads_freistellungsbescheids,
        'showUploadForm' => $funding->freistellungsbescheid_status != Funding::STATUS_VERIFIED_BY_ADMIN,
        'validationMessage' => '',
        'multiple' => false,
    ]);

echo '</fieldset>';
