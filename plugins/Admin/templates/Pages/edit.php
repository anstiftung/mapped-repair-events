<?php
use Cake\Core\Configure;
  $this->element('addScript', array('script' => 
    JS_NAMESPACE.".Helper.doCurrentlyUpdatedActions(".$isCurrentlyUpdated.");".
    JS_NAMESPACE.".Helper.bindCancelButton(".$uid.");".
    JS_NAMESPACE.".Helper.layoutEditButtons();
  "));
  echo $this->element('highlightNavi', ['main' => 'Seiten']);
?>

<div class="admin edit">

    <div class="edit">
    
      <?php echo $this->element('heading', ['first' => 'Seite bearbeiten']); ?>
      
      <?php
        echo $this->Form->create($page, ['novalidate']);
        echo $this->Form->hidden('referer', ['value' => $referer]);
        echo $this->Form->control('Pages.name').'<br />';
        echo $this->element('urlEditField', [
            'type' => 'Pages',
            'urlPrefix' => '/seite/',
            'type_de' => 'die Seite',
            'data' => $page
        ]).'<br />';
        
        
        echo $this->Form->control('Pages.menu_type', [
            'type' => 'select',
            'label' => 'In welchem Menü soll die Seite angezeigt werden?',
            'options' => $this->Html->getMenuTypes(),
            'escape' => false
        ]);
        echo $this->Form->control('Pages.parent_uid', [
            'type' => 'select',
            'label' => 'Übergeordneter Menüpunkt',
            'empty' => 'Übergeordneten Menüpunkt auswählen...',
            'options' => $pagesForSelect,
            'escape' => false
        ]);
        echo $this->Form->control('Pages.position', [
            'class' => 'short',
            'label' => 'Reihenfolge: Hauptmenu: 1-99, Untermenü: 100-999',
            'type' => 'text',
            'escape' => false
        ]);
        
        echo $this->Form->control('Pages.status', ['type' => 'select', 'options' => Configure::read('AppConfig.status')]).'<br />';
        echo $this->element('metatagsFormfields', ['entity' => 'Pages']);
    ?>
    </div>
    
    <?php
        echo $this->element('cancelAndSaveButton');
    ?>

    <div class="ckeditor-edit">
        <?php
            echo $this->element('ckeditorEdit', [
              'value' => $page->text,
              'name' => 'Pages.text',
              'uid' => $uid,
              'objectType' => 'pages'
             ]
           );
        ?>
    </div>

  <?php
    echo $this->Form->end();
  ?>


</div>

<div class="sc"></div> <?php /* wegen ckeditor */ ?>