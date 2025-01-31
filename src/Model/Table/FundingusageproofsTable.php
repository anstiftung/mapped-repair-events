<?php
declare(strict_types=1);
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Event\EventInterface;
use ArrayObject;
use App\Controller\Component\StringComponent;

class FundingusageproofsTable extends Table
{

    const MAIN_DESCRIPTION_MIN_LENGTH = 250;
    const MAIN_DESCRIPTION_MAX_LENGTH = 1500;
    const MAIN_DESCRIPTION_ERROR_MESSAGE = self::MAIN_DESCRIPTION_MIN_LENGTH . ' bis ' . self::MAIN_DESCRIPTION_MAX_LENGTH . ' Zeichen';

    const SUB_DESCRIPTION_MIN_LENGTH = 50;
    const SUB_DESCRIPTION_MAX_LENGTH = 250;
    const SUB_DESCRIPTION_ERROR_MESSAGE = self::SUB_DESCRIPTION_MIN_LENGTH . ' bis ' . self::SUB_DESCRIPTION_MAX_LENGTH . ' Zeichen';

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->addBehavior('Timestamp');
    }

    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void
    {
        $data = StringComponent::cleanAllStringsInData($data);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->lengthBetween(
            'main_description',
            [
                self::MAIN_DESCRIPTION_MIN_LENGTH,
                self::MAIN_DESCRIPTION_MAX_LENGTH,
            ],
            self::MAIN_DESCRIPTION_ERROR_MESSAGE,
        );
        $validator->lengthBetween(
            'sub_description',
            [
                self::SUB_DESCRIPTION_MIN_LENGTH,
                self::SUB_DESCRIPTION_MAX_LENGTH,
            ],
            self::SUB_DESCRIPTION_ERROR_MESSAGE,
        );

        return $validator;
    }

}

?>