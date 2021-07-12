<?php
use Cake\Core\Configure;

    if ($isEditMode) {
        $this->element('addScript', array('script' =>
            JS_NAMESPACE.".Helper.doCurrentlyUpdatedActions(".$isCurrentlyUpdated.");"
        ));
    }

    $this->element('addScript', array('script' =>
        JS_NAMESPACE.".Helper.bindCancelButtonWithFixedRedirect('".$infoSheet->uid."', '".$this->Html->urlMyEvents()."');".
        JS_NAMESPACE.".Helper.bindSaveAndRedirectToUrlButton();".
        JS_NAMESPACE.".InfoSheet.initSubCategoryDropdown('#infosheets-category-id');".
        JS_NAMESPACE.".InfoSheet.initMainCategoryDropdown('#infosheets-new-subcategory-parent-id');".
        JS_NAMESPACE.".InfoSheet.initBrandDropdown('#infosheets-brand-id');".
        JS_NAMESPACE.".InfoSheet.initDependentRadioButtons();".
        JS_NAMESPACE.".Helper.layoutEditButtons();
    "));
?>


<div class="admin edit">

    <?php
    echo $this->Form->create($infoSheet, [
        'novalidate' => 'novalidate',
        'url' => $editFormUrl,
        'id' => 'infoSheetEditForm'
    ]);
    ?>
    <div class="edit<?php echo !$this->request->getSession()->read('isMobile') ? ' column-1' : ''; ?>">
        <div style="padding:0 10px;">
            <?php
                echo $this->element('heading', ['first' => $metaTags['title']]);
                echo $this->Form->hidden('referer', ['value' => $referer]);
                $this->Form->unlockField('referer');
                echo '<p style="margin-bottom:10px;">' . Configure::read('AppConfig.initiativeNameSingular').': <b>' . $infoSheet->event->workshop->name.'</b>';
                echo ', Termin: <b> ' . $infoSheet->event->datumstart->i18nFormat(Configure::read('DateFormat.de.DateLong2')).'</b>';
                echo ', Laufzettel-Id: ';
                if ($infoSheet->uid > 0) {
                    echo '<b>' . $infoSheet->uid . '</b>';
                } else {
                    echo 'nach dem Speichern verfügbar';
                }
                echo '</p>';
            ?>
            <a id="print-button" href="javascript:window.print();" class="button rounded gray">Drucken</a>
        </div>
    </div>

    <div class="edit<?php echo !$this->request->getSession()->read('isMobile') ? ' column-2' : ''; ?>">
    <?php

        echo $this->Form->fieldset(
            $this->Form->control('InfoSheets.category_id', [
                'type' => 'select',
                'label' => 'Kategorie:',
                'options' => $categoriesForSubcategory,
                'class' => 'no-select2',
                'empty' => 'Bitte Kategorie auswählen...'
            ]).'<br />'.
            $this->Form->control('InfoSheets.new_subcategory_parent_id', [
                'templates' => [
                    'inputContainer' => $this->Html->addClassToFormInputContainer('add-subcategory'),
                    'inputContainerError' => $this->Html->addClassToFormInputContainerError('add-subcategory')
                ],
                'type' => 'select',
                'label' => 'Oberkategorie:',
                'options' => $categories,
                'empty' => 'Bitte Oberkategorie auswählen...'
            ]).
            $this->Form->control('InfoSheets.new_subcategory_name', [
                'templates' => [
                    'inputContainer' => $this->Html->addClassToFormInputContainer('add-subcategory'),
                    'inputContainerError' => $this->Html->addClassToFormInputContainerError('add-subcategory')
                ],
                'type' => 'text',
                'label' => 'Unterkategorie:'
            ]).
            $this->Form->control('InfoSheets.brand_id', [
                'type' => 'select',
                'label' => 'Marke:',
                'options' => $brands,
                'empty' => 'Bitte Marke auswählen...'
            ]).'<br />'.
            $this->Form->control('InfoSheets.new_brand_name', [
                'templates' => [
                    'inputContainer' => $this->Html->addClassToFormInputContainer('add-brand'),
                    'inputContainerError' => $this->Html->addClassToFormInputContainerError('add-brand')
                ],
                'type' => 'text',
                'label' => 'Marke:'
            ]).
            $this->Form->control('InfoSheets.device_name', ['div' => 'input text long', 'type' => 'text', 'label' => 'Modell:']).'<br />'.
            $this->Form->control('InfoSheets.device_age', ['div' => 'input text long', 'type' => 'text', 'label' => 'Alter in Jahren:']).'<br />'.
            '<div class="form-fields-checkbox-wrapper power_supply">'.
                '<label>'.$powerSupplyFormField->name.':</label>'.
                $this->Form->control('InfoSheets.form_field_options._ids', [
                    'multiple' => 'checkbox',
                    'options' => $powerSupplyFormField->preparedOptions,
                    'label' => false
                ]).
            '</div>',
            [
                'legend' => 'Gerät / Gegenstand'
            ]
        );
    ?>
    </div>

    <div class="edit<?php echo !$this->request->getSession()->read('isMobile') ? ' column-2' : ''; ?>">
    <?php
        echo $this->Form->fieldset(
            $this->Form->control('InfoSheets.defect_description', ['type' => 'textarea', 'label' => 'Fehlerbeschreibung:', 'placeholder' => 'Helft mit einer genauen Fehlerbeschreibung, wiederkehrende Defekte herauszufinden und so Schwachstellen in der Konstruktion von Geräten zu identifizieren! Maximal 1.000 Zeichen.', 'maxlength' => 1000]).
            $this->Html->generateGenericRadioButton($this->Form, $defectFoundFormField).
            $this->Html->generateGenericRadioButton($this->Form, $defectFoundReasonFormField).
            $this->Html->generateGenericRadioButton($this->Form, $repairPostponedReasonFormField).
            $this->Html->generateGenericRadioButton($this->Form, $noRepairReasonFormField).
            $this->Html->generateGenericRadioButton($this->Form, $deviceMustNotBeUsedAnymoreFormField).
            '<div class="dependent-form-field no_repair_reason_text_wrapper">'.
            $this->Form->control('InfoSheets.no_repair_reason_text', ['type' => 'textarea', 'label' => 'Sonstiges:', 'placeholder' => 'Maximal 200 Zeichen.', 'maxlength' => 200]).
           '</div>',
            [
                'legend' => 'Defekt'
            ]
        );

        $showSaveAndRedirectToUrlButton = false;
        if ($infoSheet->uid == 0) {
            $showSaveAndRedirectToUrlButton = [
                'label' => 'Speichern und neuen Laufzettel für diesen Termin öffnen',
                'redirectUrl' => $this->getRequest()->getRequestTarget(),
            ];
        }
    ?>
    </div>

    <div class="edit<?php echo !$this->request->getSession()->read('isMobile') ? ' column-1' : ''; ?>">
        <?php
            echo $this->element('cancelAndSaveButton', [
                'saveLabel' => 'Speichern',
                'showSaveAndRedirectToUrlButton' => $showSaveAndRedirectToUrlButton,
            ]);
        ?>
    </div>
    <?php
        echo $this->Form->end();
    ?>

</div>
