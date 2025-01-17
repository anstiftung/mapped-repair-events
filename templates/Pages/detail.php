<?php
declare(strict_types=1);
  echo $this->element('highlightNavi' ,['main' => $page->name]);
  echo $this->element('heading', ['first' => $page->name]);
?>

<div class="text-wrapper">
  <?php
    echo $page->text;
?>

</div>
