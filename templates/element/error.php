<?php
declare(strict_types=1);

use Cake\Core\Configure;

if (Configure::read('debug')) {
    $this->layout = 'dev_error';
    $this->assign('title', $message);
    $this->start('file');
    $this->end();
} else {
    $this->layout = 'default';
    echo '<h2>'.$message.'</h2>';
}
?>
