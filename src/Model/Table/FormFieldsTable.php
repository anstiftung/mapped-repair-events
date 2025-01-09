<?php
declare(strict_types=1);
namespace App\Model\Table;

use Cake\ORM\Table;

class FormFieldsTable extends Table
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->hasMany('FormFieldOptions', [
            'foreignKey' => 'form_field_id'
        ]);
    }

    public function getForForm($formFieldId)
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