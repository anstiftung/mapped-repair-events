<?php

echo '<h2 class="metatags">Metatags</h2>';
echo $this->Form->control($entity.'.metatag.title', ['label' => 'Title']).'<br />';
echo $this->Form->control($entity.'.metatag.keywords', ['label' => 'Keywords']).'<br />';
echo $this->Form->control($entity.'.metatag.description', ['label' => 'Description']).'<br />';

?>