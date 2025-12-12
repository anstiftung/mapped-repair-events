<?php
declare(strict_types=1);
namespace App\Model\Table;

use App\Controller\Component\StringComponent;
use Cake\Validation\Validator;
use Cake\Event\EventInterface;
use ArrayObject;

/**
 * @extends \App\Model\Table\AppTable<\App\Model\Entity\Fundingsupporter>
 */
class FundingsupportersTable extends AppTable
{

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
        if (isset($data['iban'])) {
            $data['iban'] = StringComponent::removeWhitespace($data['iban']);
        }
        if (isset($data['bic'])) {
            $data['bic'] = StringComponent::removeWhitespace($data['bic']);
        }
        if (isset($data['website'])) {
            $data['website'] = StringComponent::addProtocolToUrl($data['website']);
        }
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->notEmptyString('name', 'Bitte trage den Namen ein.');
        $validator->notEmptyString('legal_form', 'Bitte trage die Rechtsform ein.');
        $validator->notEmptyString('city', 'Bitte trage die Stadt ein.');
        $validator->minLength('city', 2, 'Bitte trage die Stadt ein.');
        $validator->notEmptyString('zip', 'Bitte trage die PLZ ein.');
        $validator->notEmptyString('street', 'Bitte trage die Straße ein.');
        $validator->minLength('street', 2, 'Bitte trage die Straße ein.');
        $validator->url('website', 'Bitte trage eine gültige Url ein.');
        $validator->add('zip', 'validFormat', [
            'rule' => ['custom', ZIP_REGEX_DE],
            'message' => 'Die PLZ ist nicht gültig.'
        ]);

        $validator->notEmptyString('contact_firstname', 'Bitte trage den Vornamen ein.');
        $validator->notEmptyString('contact_lastname', 'Bitte trage den Nachnamen ein.');
        $validator->notEmptyString('contact_phone', 'Bitte trage die Telefonnummer ein.');
        $validator->minLength('contact_function', 5, 'Bitte trage die Funktion ein (min. 5 Zeichen).');
        $validator->notEmptyString('contact_email', 'Bitte trage die E-Mail-Adresse ein.');
        $validator->email('contact_email', true, 'Bitte trage eine gültige E-Mail-Adresse ein.');

        $validator->add('name', 'unique', [
            'rule' => 'validateUnique',
            'provider' => 'table',
            'message' => 'Dieser Träger wird bereits in einem anderen Förderantrag verwendet.'
        ]);
        $validator->add('contact_email', 'unique', [
            'rule' => 'validateUnique',
            'provider' => 'table',
            'message' => 'Diese E-Mail-Adresse wird bereits in einem anderen Förderantrag verwendet.'
        ]);

        $validator->notEmptyString('bank_account_owner', 'Bitte trage den Kontoinhaber ein.');
        $validator->notEmptyString('bank_institute', 'Bitte trage das Kreditinstitut ein.');
        $validator->add('iban', 'iban', [
            'rule' => 'iban',
            'message' => 'Bitte trage eine gültige IBAN ein.'
        ]);
        $validator->add('bic', 'validFormat', [
            'rule' => ['custom', BIC_REGEX],
            'message' => 'Bitte trage einen gültigen BIC ein'
        ]);
        return $validator;
    }

}

?>