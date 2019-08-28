<?php
/**
 * @param string $this->FormId
 * @param string $model
 */
use Cake\Utility\Inflector;
if (!isset($_SESSION['FormAntiSpamKey'])) {
    $_SESSION['FormAntiSpamKey'] = $antiSpamComponent->generateKey(); 
}

echo $this->element('addScript', ['script' => "
    var sp = $('<input />');
    sp.attr('name', '".Inflector::pluralize($table)."[MappedRepairEvents_fask]');
    sp.attr('type', 'hidden');
    sp.val('".$_SESSION['FormAntiSpamKey']."');
    $('#".$formId."').prepend(sp);
"]);

?>