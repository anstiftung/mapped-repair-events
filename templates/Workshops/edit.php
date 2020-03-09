<?php
use Cake\Core\Configure;

    if ($isEditMode) {
        $this->element('addScript', array('script' =>
            JS_NAMESPACE.".Helper.doCurrentlyUpdatedActions(".$isCurrentlyUpdated.");"
        ));
    }
    if ($this->request->getSession()->read('isMobile')) {
        $this->element('addScript', ['script' =>
            JS_NAMESPACE.".MobileFrontend.putSaveAndCancelButtonToEndOfForm();
        "]);
    }
    $this->element('addScript', array('script' => 
        JS_NAMESPACE.".Helper.bindCancelButton(".$workshop->uid.");".
        JS_NAMESPACE.".Helper.layoutEditButtons();".
        JS_NAMESPACE.".Helper.initCustomCoordinatesCheckbox('#workshops-use-custom-coordinates');
    "));
    echo $this->element('highlightNavi', ['main' => 'INITIATIVEN']);
?>

<div class="admin edit">

        <?php
        echo $this->Form->create($workshop, [
            'novalidate' => 'novalidate',
            'url' => $isEditMode ? $this->Html->urlWorkshopEdit($workshop->uid) : $this->Html->urlWorkshopNew(),
            'id' => 'workshopEditForm'
        ]);
        echo $this->Form->hidden('referer', ['value' => $referer]);
        ?>
        <div class="edit">
       
           <?php echo $this->element('heading', ['first' => $metaTags['title']]); ?>
            
           <?php
            echo $this->Form->control('Workshops.name', ['label' => 'Name der Initiative']).'<br />';
            
            echo $this->element('urlEditField', [
                'type' => 'Workshops',
                'urlPrefix' => '/',
                'type_de' => 'die Initiative',
                'hidden' => true,
                'checkOriginalStatus' => true,
                'data' => $workshop
            ]).'<br />';
            
            if ($isEditMode) {
                echo '<b class="hint">Hinweis: Die Logo-Datei sollte rechteckig und mindestens 300 Pixel breit sein.</b>';
            }
            echo $this->element('upload/single', [
                 'field' => 'Workshops.image'
                ,'objectType'   => 'workshops'
                ,'image' => $workshop->image
                ,'uid' => $workshop->uid
                ,'label' => 'Logo'
            ]).'<br />';
            
            echo $this->Form->control('Workshops.street', ['label' => 'Straße + Hausnummer']).'<br />';
            echo $this->Form->control('Workshops.zip', ['label' => 'PLZ']).'<br />';
            echo $this->Form->control('Workshops.city', ['label' => 'Stadt']).'<br />';
            echo $this->Form->control('Workshops.adresszusatz', ['label' => 'Adresszusatz']).'<br />';
            echo $this->Form->control('Workshops.country_code', ['type' => 'select', 'options' => $countries, 'label' => 'Land']).'<br />';
            
            echo $this->Form->control('Workshops.use_custom_coordinates', ['type' => 'checkbox', 'label' => 'Koordinaten selbst festlegen?']).'<br />';
            echo '<div class="custom-coordinates-wrapper">';
                echo $this->Form->control('Workshops.lat', ['label' => 'Breite (Lat)', 'type' => 'text']).'<br />';
                echo $this->Form->control('Workshops.lng', ['label' => 'Länge (Long)', 'type' => 'text']).'<br />';
            echo '</div>';
            
            echo $this->Form->control('Workshops.traeger', array('label' => 'Träger')).'<br />';
            echo $this->Form->control('Workshops.rechtsform', array('label' => 'Rechtsform der RI')).'<br />';
            echo $this->Form->control('Workshops.rechtl_vertret', array('type' => 'textarea', 'label' => 'Rechtlich vertreten durch: (Name, Anschrift, Tel, Email und Funktion)' )).'<br />';
            
            
            echo $this->Form->control('Workshops.additional_contact', ['type' => 'textarea', 'label' => ['text' => 'Andere Kontaktmöglichkeiten', 'escape' => false]]).'<br />';
            echo $this->Form->control('Workshops.email', ['label' => 'E-Mail']).'<br />';
            echo $this->Form->control('Workshops.website', ['label' => 'Website']).'<br />';
            echo $this->Form->control('Workshops.feed_url', ['label' => 'RSS Feed-Url']).'<br />';
            echo '<div class="formfield-wrapper">';
                echo $this->Form->control('Workshops.facebook_username', ['type' => 'text', 'label' => 'Facebook Name']);
                echo $this->element('helpIcon', array('title' => '<p>- <b>Achtung:</b> Es können nur "Unternehmens-Seiten" verwendet werden, keine Facebook-Profile. Facebook-Profile bitte als Link in den Haupttext einfügen.</p>
                      <p>- <b>Achtung:</b> Facebook-Urls in der Form "http://www.facebook.com/people/Sdw-Neukölln/100001554702419" können leider nicht verwendet werden. Du musst eine Kurz-Url (siehe oben) verwenden.</p>
                      <p>- Der Facebook-Username ist der Teil der Facebook-Url, der "http://www.facebook.com/" folgt.</p>
                      <p>- Beispiel: http://www.facebook.com/dingfabrik => Facebook-Username: "dingfabrik"</p>
                      <p>- Bitte überprüfe nach Änderung oder Neueingabe deines Facebook-Usernamens die Darstellung auf der Frontend-Seite.</p>'));
            echo '</div>';
            echo '<div class="sc"></div>';
            
            echo $this->Form->control('Workshops.show_statistics', ['type' => 'checkbox', 'label' => 'Statistik anzeigen (falls Laufzettel-Daten verfügbar)?']).'<br />';
            
            if ($workshop->status < APP_ON) {
                echo '<div class="highlight-red">';
            }
                echo $this->Form->control('Workshops.status', [
                    'type' => 'select',
                    'options' => Configure::read('AppConfig.status'),
                ]).'<br />';
            if ($workshop->status < APP_ON) {
                echo '</div>';
            };
            
            if (!$useDefaultValidation) {
                echo $this->element('metatagsFormfields', ['entity' => 'Workshops']);
            }
        ?>
        
        <?php echo $this->element('cancelAndSaveButton', ['saveLabel' => 'Initiative speichern']); ?>
        <div class="sc"></div>
    </div>
    
    
    <div class="ckeditor-edit">
      <?php
        echo $this->element('ckeditorEdit', [
            'value' => $workshop->text,
            'name' => 'Workshops.text',
            'uid' => $workshop->uid,
            'objectType' => 'workshops'
           ]);
      ?>
    </div>

    <?php
    echo '<div class="edit categories-edit">';
        echo '<div class="categories-checkbox-wrapper">';
            echo '<b>Reparaturbereiche</b>';
            echo $this->Form->control('Workshops.categories._ids', [
                'multiple' => 'checkbox',
                'label' => false
            ]);
            echo '</div>';
    echo '</div>';
    ?>    

    <?php echo $this->Form->end(); ?>
  
</div>

<div class="sc"></div> <?php /* wegen ckeditor */ ?>