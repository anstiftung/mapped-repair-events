<?php

    use Cake\Utility\Inflector;

    echo $this->Form->create(null, [
        'method' => 'GET',
        'id' => 'admin-list-form',
    ]);

    $options = [];
    foreach($searchOptionsForDropdown as $key => $value) {
        $negate = $value['negate'] ?? false;
        $options[$key] = $value['name'] . ($negate ? ' NOT' : '');
    }

    echo $this->Form->control('key-standard', [
        'type' => 'select',
        'name' => 'key-standard',
        'options' => $options,
        'value' => $this->request->getQueryParams()['key-standard'] ??  '',
        'label' => '',
        'style' => 'width:200px;',
    ]);
    echo $this->Form->control('val-standard', [
        'label' => '',
        'name' => 'val-standard',
        'value' =>$this->request->getQueryParams()['val-standard'] ??  '',
        'style' => 'width:200px;',
    ]);
    ?>

    <?php
    if (isset($optionalSearchForms)) {
        $i = 0;
        foreach ($optionalSearchForms as $optionalSearchForm) {
            $i ++;
            echo $this->Form->control('val-opt-' . $i, [
                'type' => 'select',
                'name' => 'val-opt-' . $i,
                'empty' => $optionalSearchForm['label'],
                'options' => $optionalSearchForm['options'],
                'value' => $this->request->getQueryParams()['val-opt-' . $i] ?? '',
                'label' => '',
            ]);

            echo $this->Form->hidden('key-opt-' . $i, [
                'name' => 'key-opt-' . $i,
                'value' => $optionalSearchForm['value'],
            ]);
        }
    }
    ?>

    <?php
    if ($searchStatus) {
        echo $this->Form->control('val-status', [
            'type' => 'select',
            'name' => 'val-status',
            'empty' => '---',
            'options' => [
                APP_ON => 'online',
                APP_OFF => 'offline',
            ],
            'value' => $this->request->getQueryParams()['val-status'] ?? '',
            'label' => 'Status',
        ]);

        echo $this->Form->hidden('key-status', [
            'name' => 'key-status',
            'value' => Inflector::pluralize($objectClass) . '.status'
        ]);
    }

    echo $this->Form->hidden('sort', [
        'name' => 'sort',
        'value' => $this->request->getQueryParams()['sort'] ?? '',
    ]);

    echo $this->Form->hidden('direction', [
        'name' => 'direction',
        'value' => $this->request->getQueryParams()['direction'] ?? '',
    ]);

    echo $this->Form->button('Suchen', [
        'type' => 'submit',
        'class' => 'rounded',
    ]);

    ?>

    <?php if (!empty($this->request->getQueryParams())) { ?>
        <button type="button" class="rounded gray"
            onclick="document.location.href='/<?php echo strtolower($this->request->getParam('plugin')).'/'.Inflector::dasherize($this->request->getParam('controller')).'/'.$this->request->getParam('action');?>';">
        Filter zurücksetzen<span></span>
    </button>
    <?php } ?>

<?php echo $this->Form->end(); ?>

<div class="sc"></div>