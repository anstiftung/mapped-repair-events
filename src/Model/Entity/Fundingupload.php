<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Fundingupload extends Entity
{

    const UPLOAD_PATH = ROOT . DS . 'files_private' . DS . 'fundings' . DS;

    const TYPE_ACTIVITY_PROOF = 1;
    const TYPE_FREISTELLUNGSBESCHEID = 2;

    const TYPE_MAP = [
        self::TYPE_ACTIVITY_PROOF => 'activity_proofs',
        self::TYPE_FREISTELLUNGSBESCHEID => 'freistellungsbescheids',
    ];

    public function _getFullPath() {
        return self::UPLOAD_PATH . $this->funding_uid . DS . $this->filename;
    }

}
