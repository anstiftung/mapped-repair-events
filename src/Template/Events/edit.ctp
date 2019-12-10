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
            JS_NAMESPACE.".Helper.bindAddDateButton('".'<div class="row"><div class="input text required"><label for="0-datumstart">Datum #1</label><input type="text" name="0[datumstart]" class="datepicker-input" required="required" id="0-datumstart"/></div><div class="input time required"><label>von</label><select name="0[uhrzeitstart][hour]" required="required"><option value="" selected="selected">--</option><option value="00">0</option><option value="01">1</option><option value="02">2</option><option value="03">3</option><option value="04">4</option><option value="05">5</option><option value="06">6</option><option value="07">7</option><option value="08">8</option><option value="09">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option></select><select name="0[uhrzeitstart][minute]" required="required"><option value="" selected="selected">--</option><option value="00">00</option><option value="01">01</option><option value="02">02</option><option value="03">03</option><option value="04">04</option><option value="05">05</option><option value="06">06</option><option value="07">07</option><option value="08">08</option><option value="09">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option><option value="32">32</option><option value="33">33</option><option value="34">34</option><option value="35">35</option><option value="36">36</option><option value="37">37</option><option value="38">38</option><option value="39">39</option><option value="40">40</option><option value="41">41</option><option value="42">42</option><option value="43">43</option><option value="44">44</option><option value="45">45</option><option value="46">46</option><option value="47">47</option><option value="48">48</option><option value="49">49</option><option value="50">50</option><option value="51">51</option><option value="52">52</option><option value="53">53</option><option value="54">54</option><option value="55">55</option><option value="56">56</option><option value="57">57</option><option value="58">58</option><option value="59">59</option></select></div><div class="input time required"><label>bis</label><select name="0[uhrzeitend][hour]" required="required"><option value="" selected="selected">--</option><option value="00">0</option><option value="01">1</option><option value="02">2</option><option value="03">3</option><option value="04">4</option><option value="05">5</option><option value="06">6</option><option value="07">7</option><option value="08">8</option><option value="09">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option></select><select name="0[uhrzeitend][minute]" required="required"><option value="" selected="selected">--</option><option value="00">00</option><option value="01">01</option><option value="02">02</option><option value="03">03</option><option value="04">04</option><option value="05">05</option><option value="06">06</option><option value="07">07</option><option value="08">08</option><option value="09">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option><option value="32">32</option><option value="33">33</option><option value="34">34</option><option value="35">35</option><option value="36">36</option><option value="37">37</option><option value="38">38</option><option value="39">39</option><option value="40">40</option><option value="41">41</option><option value="42">42</option><option value="43">43</option><option value="44">44</option><option value="45">45</option><option value="46">46</option><option value="47">47</option><option value="48">48</option><option value="49">49</option><option value="50">50</option><option value="51">51</option><option value="52">52</option><option value="53">53</option><option value="54">54</option><option value="55">55</option><option value="56">56</option><option value="57">57</option><option value="58">58</option><option value="59">59</option></select></div><a class="remove-date-button" title="Termin löschen" href="javascript:void(0);"><i class="fa fa-minus-circle"></i></a></div>' . "');"
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
                } else {
                    // duplicate mode
                    echo $this->Form->hidden($i.'.workshop_uid', ['value' => $preselectedWorkshopUid]);
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
            
            if (!$isEditMode) {
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
                echo $this->Form->control($i.'.uhrzeitstart', ['type' => 'time', 'label' => __('Add Event: Start Time'), 'timeFormat' => 24, 'empty' => '--']);
                echo $this->Form->control($i.'.uhrzeitend', ['type' => 'time', 'label' => __('Add Event: End Time'), 'timeFormat' => 24, 'empty' => '--']);
            
                if (!$isEditMode) {
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

