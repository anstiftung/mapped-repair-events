<?php

  /*
  $counter = $this->Paginator->counter(); 
  if ($counter == '1 of 1' || $counter == '0 of 1') return;
  */
  if( empty($urlOptions) ) $urlOptions = ['url' => []];
  
  $named = [];
  if (!empty($this->request->getParam('named'))) {
      $named = $this->request->getParam('named');
  }
  
  $mergedUrlOptions = array_merge($named, ['escape' => false], $urlOptions);
  
  $this->Paginator->options($mergedUrlOptions);
  
  $this->element('addScript', ['script' => 
      JS_NAMESPACE.".Helper.initPagination();
  "]);

  if (!isset($objectNameSingular)) { $objectNameSingular = 'Datensatz'; };
  if (!isset($objectNamePlural)) { $objectNamePlural = 'Datensätze'; };
  
  echo '<div class="pagination">';
        echo '<div class="numbers">'.$this->Paginator->param('count').' '.($this->Paginator->param('count') == 1 ? $objectNameSingular : $objectNamePlural).' gefunden</div>';
        /*
        echo $this->Paginator->first('««', $mergedUrlOptions, null, ['class' => 'first']);
        echo $this->Paginator->prev('«', $mergedUrlOptions, null, ['class' => 'prev']);
        echo $this->Paginator->numbers(array_merge($mergedUrlOptions, ['before' => '', 'after' => '', 'separator' => '', 'modulus' => 6]));
        echo $this->Paginator->next('»', $mergedUrlOptions, null, ['class' => 'next']);
        echo $this->Paginator->last('»»', $mergedUrlOptions, null, ['class' => 'last']);
        */
    echo '</div>';
?>