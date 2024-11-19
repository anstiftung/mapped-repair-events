<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use App\Model\Entity\Fundingbudgetplan;
use App\Controller\Component\StringComponent;
use Cake\Event\EventInterface;
use ArrayObject;

class FundingbudgetplansTable extends Table
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->addBehavior('Timestamp');
        $this->belongsTo('Fundings', [
            'foreignKey' => 'funding_uid'
        ]);
    }

    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options)
    {
        $data = StringComponent::cleanAllStringsInData($data);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->add('type', 'valid', [
                'rule' => function ($value) {
                    return array_key_exists($value, Fundingbudgetplan::TYPE_MAP);
                },
                'message' => 'Bitte Förderbereich auswählen',
            ]);

        $validator->minLength('description', 5, 'Beschreibung zu kurz (zwischen 5 und 100 Zeichen)');
        $validator->maxLength('description', 100, 'Beschreibung zu lang (zwischen 5 und 100 Zeichen)');

        $validator
            ->add('amount', 'valid', [
                'rule' => 'numeric',
                'message' => 'Ungültiger Betrag'
            ]);

        $validator
            ->add('amount', 'valid', [
                'rule' => function ($value) {
                    return $value > 0;
                },
                'message' => 'Bitte Betrag eingeben',
            ]);
    

        return $validator;
    }

}

?>