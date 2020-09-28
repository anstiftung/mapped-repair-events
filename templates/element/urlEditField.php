<?php
use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
use Cake\Utility\Inflector;

$objectForEditableCheck = $data;

if (isset($checkOriginalStatus) && $checkOriginalStatus && $data->uid) {

    $objectClass = FactoryLocator::get('Table')->get($type);
    $entity = $objectClass->get($data->uid, [
        'conditions' => [
            $type.'.status >= ' . APP_DELETED
        ]
    ]);
    $objectForEditableCheck = $entity;
}

if ($this->Html->isUrlEditable($objectForEditableCheck)) {
    $entityNameForJquerySelector = strtolower(Inflector::camelize($type));
    $this->element('addScript', array('script' => "
        $('#" . $entityNameForJquerySelector . "-url').slugify('#" . $entityNameForJquerySelector . "-name');"
    ));
}

$htmlAttributes = array(
    'label' => 'Slug'
);
if (!$this->Html->isUrlEditable($objectForEditableCheck)) {
    $htmlAttributes['readonly'] = 'readonly';
}

if (isset($hidden) && $hidden) {
    $this->element('addScript', array('script' =>
        JS_NAMESPACE.".Helper.bindSlugToggle();"
    ));
    echo '<div class="formfield-wrapper url-edit-field-wrapper">';

    $display = 'none';
    $checked = '';
    if ($this->Form->isFieldError($type . '.url')) {
        $display = 'block';
        $checked = ' checked="checked"';
    }

    echo '<div class="checkbox">
              <input type="checkbox" name="show-url-edit-field" id="show-url-edit-field"'.$checked.'">
              <label for="show-url-edit-field">'.__('edit slug').'</label>
          </div>';

    $title = '<ul>';
    $title .= '<li>Die Url auf unserer Plattform besteht aus ' . Configure::read('AppConfig.serverName') . $urlPrefix . ' und einem frei wählbaren Namenszusatz, dem sogenannten "Slug".</li>
                      <li><b>Achtung:</b> Der Slug kann nur geändert werden, wenn ' . $type_de . ' auf offline gesetzt ist.</li>
                      <li>Sie sollte nur in Sonderfällen geändert werden, da das Suchmaschinenranking der Seite darunter leidet.</li>';
    if ($type != 'Coach') {
        $title .= '<li>Der Wert im Feld Slug wird automatisch verändert, sobald das darüberliegende Feld "Name" verändert wird (und '.$type_de.' auf offline gesetzt ist.</li>';
    }
    $title .= '</ul>';
    echo $this->element('helpIcon', array('title' => $title));

    echo '</div>';

    echo '<div class="url-edit-field" style="display:'.$display.'";float: left;">';
}
echo $this->Form->control($type . '.url', $htmlAttributes);

if (isset($hidden) && $hidden) {
    echo '</div>';
}

?>
