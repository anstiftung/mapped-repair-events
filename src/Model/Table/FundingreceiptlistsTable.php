<?php
declare(strict_types=1);
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use App\Model\Entity\Fundingbudgetplan;
use App\Controller\Component\StringComponent;
use Cake\Event\EventInterface;
use ArrayObject;

class FundingreceiptlistsTable extends Table
{

    const DESCRIPTION_MIN_LENGTH = 2;
    const DESCRIPTION_MAX_LENGTH = 150;
    const DESCRIPTION_ERROR_MESSAGE = self::DESCRIPTION_MIN_LENGTH . ' bis ' . self::DESCRIPTION_MAX_LENGTH . ' Zeichen';

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->addBehavior('Timestamp');
        $this->belongsTo('Fundings', [
            'foreignKey' => 'funding_uid'
        ]);
    }

    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void
    {
        $data = StringComponent::cleanAllStringsInData($data);
    }

    public function validationDefault(Validator $validator): Validator
    {

        $validator
            ->add('type', 'valid', [
                'rule' => function ($value, $context) {
                    return array_key_exists($value, Fundingbudgetplan::TYPE_MAP); // using budgetplan is ok here
                },
                'message' => 'Förderbereich auswählen',
            ]);

        $validator
            ->add('description', 'valid', [
                'rule' => function ($value, $context) {
                    $descriptionLength = strlen($value);
                    $mainCheck = $descriptionLength >= self::DESCRIPTION_MIN_LENGTH && $descriptionLength <= self::DESCRIPTION_MAX_LENGTH;
                    return $mainCheck;
                },
                'message' => self::DESCRIPTION_ERROR_MESSAGE,
            ]);

        $validator
            ->add('amount', 'valid', [
                'rule' => function ($value, $context) {
                    return $value > 0;
                },
                'message' => 'Betrag muss größer als 0 sein',
            ]);

        return $validator;
    }    

}

?>