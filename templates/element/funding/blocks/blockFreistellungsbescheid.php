<?php

use App\Model\Entity\Funding;
use App\Model\Entity\Fundingupload;

echo '<fieldset>';

    echo '<legend>Freistellungsbescheid</legend>';

    echo '<div class="verification-wrapper ' . $funding->freistellungsbescheid_status_css_class . '">';
        echo '<p>' . $funding->freistellungsbescheid_status_human_readable . '</p>';
            if ($funding->freistellungsbescheid_comment != '') {
                echo '<p class="comment">' . h($funding->freistellungsbescheid_comment) . '</p>';
            }
    echo '</div>';

    echo '<div style="margin-bottom:10px;padding:10px;">';
        echo '<p>Das Ausstellungsdatum des Bescheides muss lesbar sein. Bitte lade alle Seiten des vorläufigen/regulären Freistellungsbescheides der gemeinnützigen Trägerorganisation als ein PDF (unter 5MB) hoch.</p>';
    echo '</div>';

    echo $this->element('funding/blocks/upload/listUploadsAndUploadForm', [
        'uploadType' => Fundingupload::TYPE_MAP[Fundingupload::TYPE_FREISTELLUNGSBESCHEID],
        'fundinguploads' => $funding->fundinguploads_freistellungsbescheids,
        'showUploadForm' => $funding->freistellungsbescheid_status != Funding::STATUS_VERIFIED,
        'validationMessage' => '',
        'multiple' => false,
    ]);

echo '</fieldset>';
