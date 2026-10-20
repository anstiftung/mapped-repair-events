<?php
declare(strict_types=1);
use Cake\Core\Configure;

if (!Configure::read('useMatomo')) return;
?>

<script>
    console.log('dummy matomo script loaded');
</script>

