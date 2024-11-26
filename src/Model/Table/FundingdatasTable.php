<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Event\EventInterface;
use ArrayObject;
use App\Controller\Component\StringComponent;

class FundingdatasTable extends Table
{

    const DESCRIPTION_MIN_LENGTH = 250;
    const DESCRIPTION_MAX_LENGTH = 1500;
    const DESCRIPTION_ERROR_MESSAGE = self::DESCRIPTION_MIN_LENGTH . ' bis ' . self::DESCRIPTION_MAX_LENGTH . ' Zeichen';

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->addBehavior('Timestamp');
    }

    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options)
    {
        $data = StringComponent::cleanAllStringsInData($data);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->minLength('description', self::DESCRIPTION_MIN_LENGTH, self::DESCRIPTION_ERROR_MESSAGE);
        $validator->maxLength('description', self::DESCRIPTION_MAX_LENGTH, self::DESCRIPTION_ERROR_MESSAGE);
        return $validator;
    }

}

?>