<?php
  echo $this->element('highlightNavi' ,['main' => $page->name]);
  echo $this->element('heading', ['first' => $page->name]);
?>

<div class="text-wrapper">
  <?php
    echo $page->text;
?>

</div>
