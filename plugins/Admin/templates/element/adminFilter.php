<form action="" method="get" id="admin-list-form">

    <?php
    use Cake\Core\Configure;
use Cake\Utility\Inflector;

    echo $this->Form->control('key-standard', [
        'type' => 'select',
        'name' => 'key-standard',
        'empty' => '---',
        'options' => array_combine(array_keys($searchOptionsForDropdown), array_keys($searchOptionsForDropdown)),
        'value' => isset($this->request->getQueryParams()['key-standard']) ? $this->request->getQueryParams()['key-standard'] : '',
        'label' => ''
    ]);
    echo $this->Form->control('val-standard', [
        'label' => '',
        'name' => 'val-standard',
        'value' => isset($this->request->getQueryParams()['val-standard']) ? $this->request->getQueryParams()['val-standard'] : ''
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
                'value' => isset($this->request->getQueryParams()['val-opt-' . $i]) ? $this->request->getQueryParams()['val-opt-' . $i] : '',
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
            'value' => isset($this->request->getQueryParams()['val-status']) ? $this->request->getQueryParams()['val-status'] : '',
            'label' => 'Status'
        ]);

        echo $this->Form->hidden('key-status', [
            'name' => 'key-status',
            'value' => Inflector::pluralize($objectClass) . '.status'
        ]);
    }

    echo $this->Form->hidden('sort', [
        'name' => 'sort',
        'value' => isset($this->request->getQueryParams()['sort']) ? $this->request->getQueryParams()['sort'] : ''
    ]);

    echo $this->Form->hidden('direction', [
        'name' => 'direction',
        'value' => isset($this->request->getQueryParams()['direction']) ? $this->request->getQueryParams()['direction'] : ''
    ]);

    ?>

    <button type="submit" class="rounded">Suchen</button>

    <?php if (!empty($this->request->getQueryParams())) { ?>
        <button type="button" class="rounded gray"
            onclick="document.location.href='/<?php echo strtolower($this->request->getParam('plugin')).'/'.strtolower($this->request->getParam('controller')).'/'.$this->request->getParam('action');?>';">
        Filter zurücksetzen<span></span>
    </button>
    <?php } ?>

</form>
<div class="sc"></div>