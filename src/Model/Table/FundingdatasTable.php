<?php
declare(strict_types=1);
namespace App\Model\Table;

use Cake\Validation\Validator;
use Cake\Event\EventInterface;
use ArrayObject;
use App\Controller\Component\StringComponent;

/**
 * @extends \App\Model\Table\AppTable<\App\Model\Entity\Fundingdata>
 */
class FundingdatasTable extends AppTable
{

    const DESCRIPTION_MIN_LENGTH = 250;
    const DESCRIPTION_MAX_LENGTH = 1500;
    const DESCRIPTION_ERROR_MESSAGE = self::DESCRIPTION_MIN_LENGTH . ' bis ' . self::DESCRIPTION_MAX_LENGTH . ' Zeichen';

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->addBehavior('Timestamp');
    }

    /**
     * @param \Cake\Event\EventInterface<\Cake\Datasource\EntityInterface> $event
     * @param \ArrayObject<string, mixed> $data
     * @param \ArrayObject<string, mixed> $options
     */
    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void
    {
        $data = StringComponent::cleanAllStringsInData($data);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->lengthBetween(
            'description',
            [
                self::DESCRIPTION_MIN_LENGTH,
                self::DESCRIPTION_MAX_LENGTH,
            ],
            self::DESCRIPTION_ERROR_MESSAGE,
        );
        return $validator;
    }

}

?>