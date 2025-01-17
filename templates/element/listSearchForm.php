<?php
declare(strict_types=1);
use Cake\Core\Configure;
?>

<p style="margin:15px 0 5px 0;"><?php echo $label; ?></p>

<?php
echo $this->Form->create(null, [
    'id' => 'list-search-form',
    'type' => 'get',
]);

echo $this->Form->control('keyword', ['label' => '', 'value' => $keyword, 'disabled' => true]);
if (!empty($categories)) {
    echo $this->Form->hidden('categories', ['label' => '', 'value' => $categories]);
}
if (isset($provinces)) {
    echo $this->Form->control('provinceId', ['type' => 'select', 'label' => '', 'options' => $provinces, 'value' => $provinceId, 'empty' => 'Bundesland / Kanton', 'disabled' => true]);
}
if (Configure::read('AppConfig.onlineEventsEnabled') && $showIsOnlineEventCheckbox) {
    echo $this->Form->control('isOnlineEvent', ['hiddenField' => false, 'type' => 'checkbox', 'label' => 'Online-Event?', 'checked' => $isOnlineEvent, 'disabled' => true]);
}
echo $this->Form->button(__('Search'), [
    'type' => 'submit',
    'class' => 'button filter',
]);

if ($resetButton) { ?>
    <a href="<?php echo $baseUrl; ?>" class="button gray"><?php echo __('Clear'); ?></a>
<?php }
