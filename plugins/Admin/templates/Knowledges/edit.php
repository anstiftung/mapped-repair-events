<?php
use Cake\Core\Configure;

    $this->element('addScript', array('script' =>
        JS_NAMESPACE.".Helper.doCurrentlyUpdatedActions(".$isCurrentlyUpdated.");".
        JS_NAMESPACE.".Helper.bindCancelButton(".$uid.");".
        JS_NAMESPACE.".Helper.layoutEditButtons();
    "));
    echo $this->element('highlightNavi', ['main' => 'Reparaturwissen']);
?>

<div class="admin edit">

        <div class="edit">

        <?php echo $this->element('heading', ['first' => 'Reparaturwissens-Beitrag bearbeiten']); ?>

        <?php
            echo $this->Form->create(
                $knowledge,
                [
                    'novalidate' => 'novalidate',
                    'id' => 'knowledgeEditForm'
                ]
            );
            echo $this->Form->hidden('referer', ['value' => $referer]);
            $this->Form->unlockField('referer');

            echo $this->Form->control('Knowledges.title', ['label' => 'Titel']).'<br />';
            echo $this->Form->control('Knowledges.status', ['type' => 'select', 'options' => Configure::read('AppConfig.status')]).'<br />';

            echo $this->element('metatagsFormfields', ['entity' => 'Knowledges']);


            echo '<div class="categories-checkbox-wrapper">';
                echo '<b id="knowledges-categories" class="pseudo-field">' . Configure::read('AppConfig.categoriesNameUsers') . '</b>';
                echo $this->Form->control('Knowledges.categories._ids', [
                    'multiple' => 'checkbox',
                    'label' => false,
                ]);
            echo '</div>';
            echo '<div class="sc"></div>';

            $this->element('addScript', ['script' => 
                JS_NAMESPACE . ".Helper.addNewTagsToSelect2Multidropdown('select#knowledges-skills-ids', ".json_encode($this->request->getSession()->read('newSkillsKnowledges')).");
            "]);

            echo '<div class="skills-wrapper">';
                echo '<b id="knowledges-skills" class="pseudo-field">Weitere Kenntnisse / Interessen</b>';
                echo $this->Form->control('Knowledges.skills._ids', [
                    'multiple' => 'select',
                    'data-tags' => true,
                    'data-token-separators' => "[',']",
                    'label' => false,
                    'options' => $skillsForDropdown,
                ]);
            echo '</div>';

        ?>
    </div>

    <?php
        echo $this->element('cancelAndSaveButton');
    ?>
    <div class="editor-edit">
      <?php
        echo $this->element('editorEdit', [
           'value' => $knowledge->text,
           'name' => 'Knowledges.text',
           'uid' => $uid,
           'objectType' => 'knowledges'
         ]
       );
      ?>
    </div>

      <?php
        echo $this->Form->end();
      ?>

</div>

<div class="sc"></div> <?php /* wegen editor */ ?>