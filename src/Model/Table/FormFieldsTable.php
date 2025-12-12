<?php
declare(strict_types=1);
namespace App\Model\Table;

use App\Model\Entity\FormField;

/**
 * @extends \App\Model\Table\AppTable<\App\Model\Entity\FormField>
 */
class FormFieldsTable extends AppTable
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->hasMany('FormFieldOptions', [
            'foreignKey' => 'form_field_id'
        ]);
    }

    public function getForForm(int $formFieldId): FormField
    {
        $formField = $this->find('all',
        conditions: [
            'FormFields.id' => $formFieldId,
            'FormFields.status' => APP_ON
        ],
        contain: [
            'FormFieldOptions' => [
                'sort' => [
                    'FormFieldOptions.rank' => 'ASC'
                ]
            ]
        ])->first();

        $preparedFormFieldOptions = [];
        foreach($formField->form_field_options as $formFieldOption) {
            $preparedFormFieldOptions[$formFieldOption->value] = $formFieldOption->name;
        }

        $formField->preparedOptions = $preparedFormFieldOptions;

        return $formField;
    }

}

?>