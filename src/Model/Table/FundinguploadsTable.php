<?php
namespace App\Model\Table;

use Cake\ORM\Table;

class FundinguploadsTable extends Table
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->addBehavior('Timestamp');
        $this->belongsTo('Fundings', [
            'foreignKey' => 'funding_uid'
        ]);
    }

}

?>