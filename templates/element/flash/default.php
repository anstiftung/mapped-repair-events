<?php
declare(strict_types=1);
?>
<div id="flashMessage" class="<?php echo $params['class']; ?>">
    <?php
        echo $message;
        echo '<a class="closer" title="Schließen" href="javascript:void(0);"><i class="fas fa-times-circle fa-lg"></i></a>';
    ?>
</div>