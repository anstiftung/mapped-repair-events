<?php
use Cake\Core\Configure;

echo $this->element('highlightNavi', ['main' => 'TERMINE']);
$this->element('addScript', ['script' => 
    JS_NAMESPACE.".Helper.initEventAllForm('body.events.all #list-search-form');".
    JS_NAMESPACE.".Helper.bindTooltip('body.events.all #category-icons a');
"]);
?>

<div class="left">

    <div class="top">
        <h1>Gesamtansicht Reparaturtermine</h1>
        
        <?php echo $this->element('listSearchForm', [
        	'baseUrl' => '/reparatur-termine',
       	    'keyword' => $keyword,
            'categories' => $this->request->getQuery('categories'),
            'resetButton' => (($keyword != '' || count($selectedCategories) > 0) ? true : false),
            'label' => 'Suche nach Initiativen, PLZ und Orten'
        ]);
        ?>
    
        <div class="sc"></div>
        <div id="category-icons">
        	<?php
        	    foreach($preparedCategories as $category) {
            		echo '<a href="'.$category['href'].'"
                             title="'.$category['name'].'"
                             id="category-'.$category['id'].'"
                             class="lstCat sklill_icon '.$category['icon'].' '.$category['class'] .'">
                    </a>';
            		
            	}
        	?>
        	<a class="button" href="<?php echo $resetCategoriesUrl; ?>" ><?php echo __('Select all'); ?></a>
       </div>
   </form>
        
    <?php
    $paginationParams = ['objectNameSingular' => 'Reparaturtermin', 'objectNamePlural' => 'Reparaturtermine'];
    echo $this->element('pagination', $paginationParams);

    echo '</div>'; // div.top
    foreach($events as $event) {
   	
        echo '<div class="item-wrapper">';
        	
            echo '<a title="'.$event->workshop->name.'" href="'.$this->Html->urlWorkshopDetail($event->directurl).'#datum">';

                $eventImage = $event->image;
                $eventImageType = 'events';
                
                // take workshop image if events image is empty
                if ($eventImage == '') {
                    $eventImage = $event->workshop->image;
                    $eventImageType = 'workshops';
                }
                // take rclogo if workshop image empty too
                if ($eventImage == '') {
                    $eventImage = 'rclogo-100.jpg';
                }
        	    echo '<img title="'.$event->image_alt_text.'"
                           alt="'.$event->image_alt_text.'" class="detail-image" 
                           src="'.$this->Html->getThumbs100Image($eventImage, $eventImageType).'" />';
        	    
            echo '</a>';
            
            echo '<a class="title" href="'.$this->Html->urlWorkshopDetail($event->directurl).'#datum">'.$event->datumstart->i18nFormat(Configure::read('DateFormat.de.DateLong2')) . ' // ' . $event->workshop->name.' // ' . ' ' . $event->ort.'</a>';
            echo '<div class="text-wrapper">';
            echo '<p>Der Termin beginnt um <b>' . $event->uhrzeitstart->i18nFormat(Configure::read('DateFormat.de.TimeShort')) .'</b> und endet um <b>' . $event->uhrzeitend->i18nFormat(Configure::read('DateFormat.de.TimeShort')).' Uhr</b>.</p>';
            echo '</div>';
            
            foreach($preparedCategories as $preparedCategory) {
                $categoryClass = 'not-selected';
                foreach($event->categories as $category) {
                    if ($category->id == $preparedCategory['id']) {
                        $categoryClass = 'selected';
                    }
                }
                echo '<div
                         title="'.$preparedCategory['name'].'"
                         class="lstCatNew sklill_icon small '.$preparedCategory['icon'].' '.$categoryClass .'">
                    </div>';
            }
            
        echo '</div>';
        
    }
    if (count($events) > 0) {
        echo $this->element('pagination', $paginationParams);
    }
?>

</div> <?php // left column ends here ?>

<?php if (!$this->request->getSession()->read('isMobile')) { ?>

    <div class="right">
    
        <?php
            
            $jqueryString = "var map = new ".JS_NAMESPACE.".Map(".json_encode($eventsForMap).");";
            $jqueryString .= "map.objectType = 'Event';";
            if ($keyword != '' || count($selectedCategories) > 0) {
                $jqueryString .= "map.loadAllEvents('".$keyword."', '".join(',', $selectedCategories)."');";
            } else {
                $jqueryString .= "map.initMarkers();";
            }
            $jqueryString .= "map.setMapAsFixed(($('#header').height() + 8));";
            $this->element('addScript', ['script' => $jqueryString]);
            
        ?>
            
        <div id="mapContainer">
            <div id="map"></div>
        </div>
            
    </div><?php // right column ends here ?>
    
<?php } ?>