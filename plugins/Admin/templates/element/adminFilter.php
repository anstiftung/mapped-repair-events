<?php
    echo $this->Form->create(null, [
        'method' => 'POST',
        'id' => 'admin-list-form',
    ]);

    use Cake\Utility\Inflector;

    echo $this->Form->control('key-standard', [
        'type' => 'select',
        'name' => 'key-standard',
        'empty' => '---',
        'options' => array_combine(array_keys($searchOptionsForDropdown), array_keys($searchOptionsForDropdown)),
        'value' => $this->request->getData('key-standard', ''),
        'label' => ''
    ]);
    echo $this->Form->control('val-standard', [
        'label' => '',
        'name' => 'val-standard',
        'value' => $this->request->getData('val-standard', ''),
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
                'value' => $this->request->getData('val-opt-' . $i, ''),
                'label' => ''
            ]);

            echo $this->Form->hidden('key-opt-' . $i, [
                'name' => 'key-opt-' . $i,
                'value' => $optionalSearchForm['value']
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
                APP_OFF => 'offline'
            ],
            'value' => $this->request->getData('val-status', ''),
            'label' => 'Status'
        ]);

        echo $this->Form->hidden('key-status', [
            'name' => 'key-status',
            'value' => Inflector::pluralize($objectClass) . '.status'
        ]);
    }

    echo $this->Form->hidden('sort', [
        'name' => 'sort',
        'value' => $this->request->getData('sort', ''),
    ]);

    echo $this->Form->hidden('direction', [
        'name' => 'direction',
        'value' => $this->request->getData('direction', ''),
    ]);

    echo $this->Form->button('Suchen', [
        'type' => 'submit',
        'class' => 'rounded',
    ]);

    ?>

    <?php if (!empty($this->request->getData())) { ?>
        <button type="button" class="rounded gray"
            onclick="document.location.href='/<?php echo strtolower($this->request->getParam('plugin')).'/'.Inflector::dasherize($this->request->getParam('controller')).'/'.$this->request->getParam('action');?>';">
        Filter zurücksetzen<span></span>
    </button>
    <?php } ?>

<?php echo $this->Form->end(); ?>

<div class="sc"></div>