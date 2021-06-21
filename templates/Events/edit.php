<?php
use Cake\Core\Configure;

if ($this->request->getSession()->read('isMobile')) {
    $this->element('addScript', ['script' =>
        JS_NAMESPACE.".MobileFrontend.putSaveAndCancelButtonToEndOfForm();
        "]);
}
$this->element('addScript', ['script' =>
    JS_NAMESPACE.".Helper.bindCancelButton(".$events[0]->uid.");".
    JS_NAMESPACE.".Helper.layoutEditButtons();".
    JS_NAMESPACE.".Helper.initCustomCoordinatesCheckbox('#0-use-custom-coordinates');
        $('.edit .apply-workshop-data-button').on('click', function() { ".
    JS_NAMESPACE.".Helper.bindApplyWorkshopButton($(this));
        });
    "]);
    echo $this->element('datepicker');

    if ($isEditMode) {
        $this->element('addScript', ['script' =>
            JS_NAMESPACE.".Helper.doCurrentlyUpdatedActions(".$isCurrentlyUpdated.");"
        ]);
    }

    if (!$isEditMode) {
        $this->element('addScript', ['script' =>
            JS_NAMESPACE.".Helper.bindRemoveDateButton();".
            JS_NAMESPACE.".Helper.bindAddDateButton('".'<div class="row"><div class="input text required"><label for="0-datumstart">Datum #1</label><input type="text" name="0[datumstart]" class="datepicker-input" required="required" id="0-datumstart"/></div><div class="time-fields-wrapper"><div class="input time required"><label for="0-uhrzeitstart">von</label><input type="time" name="0[uhrzeitstart]" timeformat="24" required="required" id="0-uhrzeitstart" step="1" value=""></div><div class="input time required"><label for="0-uhrzeitend">bis</label><input type="time" name="0[uhrzeitend]" timeformat="24" required="required" id="0-uhrzeitend" step="1" value=""></div></div><a class="remove-date-button" title="Termin löschen" href="javascript:void(0);"><i class="fa fa-minus-circle"></i></a></div>' . "');"
        ]);
    }
    ?>

<div class="admin edit">
    <div class="edit">

       <?php echo $this->element('heading', ['first' => $metaTags['title']]); ?>

       <?php
        echo $this->Form->create($events, [
            'novalidate' => 'novalidate',
            'url' => $editFormUrl,
            'id' => 'eventEditForm'
        ]);
        echo $this->Form->hidden('referer', ['value' => $referer]);

        foreach($unlockedFields as $unlockedField) {
            $this->Form->unlockedFields($unlockedField);
        }

        $i = 0;
        foreach($events as $event) {

            if ($i > 0) continue;

            if (isset($preselectedWorkshopUid)) {
                if (!empty($workshopsForDropdown)) {
                    // add mode
                    echo $this->Form->control($i.'.workshop_uid', [
                        'type' => 'select',
                        'options' => $workshopsForDropdown,
                        'default' => $preselectedWorkshopUid,
                        'empty' => '== Initiative auswählen ==',
                        'label' => __('Workshop')
                    ]);
                }
                if ($isDuplicateMode) {
                    echo $this->Form->hidden($i.'.workshop_uid', ['value' => $preselectedWorkshopUid, 'id' => '0-workshop-uid']);
                }
            }


            if (!empty($event->workshop->name)) {
                echo '<div class="input">';
                echo '<label>' . Configure::read('AppConfig.initiativeNameSingular').'</label>';
                echo '<b style="margin-top:-2px;float:left;">'.$event->workshop->name.'</b>';
                echo '</div>';
            }

            if ($isEditMode) {
                echo $this->element('hint', [
                    'content' => 'Die Logo-Datei sollte rechteckig und mindestens 300 Pixel breit sein.'
                ]);
            }
            echo $this->element('upload/single', [
                'field' => $i.'.image'
                ,'objectType'   => 'events'
                ,'image' => $events[0]->image
                ,'uid' => $events[0]->uid
                ,'label' => 'Terminbild'
            ]).'<br />';

            if (!$isEditMode && !$isDuplicateMode) {
                echo $this->element('hint', [
                    'content' => 'Durch Klicken auf das Plus-Zeichen rechts kannst du mehrere Termine auf einmal erstellen.<br />Nach dem Speichern kannst du sie wie gewohnt einzeln bearbeiten.'
                ]);
            }

            echo '<div class="date-time-wrapper">';

            $i++;

        }

        $i = 0;
        foreach($events as $event) {

            echo '<div class="row">';

                $indexForLabel = $i + 1;
                echo $this->Form->control($i.'.datumstart',  ['class' => 'datepicker-input', 'type' => 'text', 'label' => __('Add Event: Start Date') . ' #' . $indexForLabel, 'value' => !empty($event->datumstart) ? $event->datumstart->i18nFormat(Configure::read('DateFormat.de.DateLong2')) : '']);
                echo '<div class="time-fields-wrapper">';
                    echo $this->Form->control($i.'.uhrzeitstart', ['label' => __('Add Event: Start Time'), 'step' => 0]);
                    echo $this->Form->control($i.'.uhrzeitend', ['label' => __('Add Event: End Time'), 'step' => 0]);
                echo '</div>';

                if (!$isEditMode && !$isDuplicateMode) {
                    if ($i == 0) {
                        echo '<a class="add-date-button" title="Termin hinzufügen" href="javascript:void(0);"><i class="fa fa-plus-circle"></i></a>';
                    } else {
                        echo '<a class="remove-date-button" title="Termin löschen" href="javascript:void(0);"><i class="fa fa-minus-circle"></i></a>';
                    }
                }

            echo '</div>';

            $i++;

        }

        $i = 0;
        foreach($events as $event) {

            if ($i > 0) continue;

            echo '</div>';
            echo '<div class="sc"></div>';

            echo $this->Form->control($i.'.eventbeschreibung', ['label' => __('Add Event: event description')]).'<br />';
            echo $this->Form->control($i.'.is_online_event', ['type' => 'checkbox', 'label' => 'Online-Termin?']).'<br />';

            if (!$isEditMode && isset($preselectedWorkshopUid)) {
                echo '<br /><br />';
                echo '<a class="button apply-workshop-data-button" href="javascript:void(0);">Profildaten übernehmen</a><img class="ajaxLoader" src="/img/ajax-loader.gif" width="32" height="32" />';
            }

            echo $this->Form->control($i.'.veranstaltungsort', ['div' => 'input text long', 'type' => 'text','label' => __('Add Event: Venue')]).'<br />';
            echo $this->Form->control($i.'.strasse', ['type' => 'text','label' => __('Add Event: Street and Number')]).'<br />';

            echo $this->Form->control($i.'.zip', ['type' => 'text','label' => __('Add Event: Zip Code')]).'<br />';
            echo $this->Form->control($i.'.ort', ['type' => 'text','label' => __('Add Event: City')]).'<br />';
            echo $this->Form->control($i.'.land', ['type' => 'text','label' => __('Add Event: Country')]).'<br />';

            echo $this->Form->control($i.'.use_custom_coordinates', ['type' => 'checkbox', 'label' => 'Koordinaten selbst festlegen?']).'<br />';
            echo '<div class="custom-coordinates-wrapper">';
            echo $this->Form->control($i.'.lat', ['label' => 'Breite (Lat)', 'type' => 'text']).'<br />';
            echo $this->Form->control($i.'.lng', ['label' => 'Länge (Long)', 'type' => 'text']).'<br />';
            echo '</div>';

            echo '<br />';
            echo '<div class="categories-checkbox-wrapper">';
            echo '<b>Reparaturbereiche</b>';
            echo $this->Form->control($i.'.categories._ids', [
                'multiple' => 'checkbox',
                'label' => false
            ]);
            echo '</div>';
            echo '<div class="sc"></div>';

            if ($isEditMode) {
                echo $this->Form->control($i.'.renotify', ['type' => 'checkbox', 'label' => 'Ich habe diesen Termin überarbeitet und möchte, dass alle Interessenten darüber nochmals informiert werden.']).'<br />';
            }

            echo $this->element('hint', [
                'content' => 'Achtung! Wenn der Status des Termins auf „online“ gesetzt ist und du auf speichern klickst, dann ist der Termin sofort sichtbar und alle Interessenten, die deiner ' . Configure::read('AppConfig.initiativeNameSingular').' folgen ("Ich möchte über anstehende Veranstaltungen dieser Initiative per E-Mail informiert werden") werden informiert.'
            ]);
            echo $this->Form->control($i.'.status', ['type' => 'select', 'options' => Configure::read('AppConfig.status')]).'<br />';

            if (!$useDefaultValidation) {
                echo $this->element('metatagsFormfields', ['entity' => $i]);
            }

            $i++;
        }

    ?>

      <?php echo $this->element('cancelAndSaveButton', ['saveLabel' => 'Speichern']); ?>
    <div class="sc"></div>

    <?php echo $this->Form->end(); ?>

    </div>
</div>

