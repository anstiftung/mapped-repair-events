<?php
declare(strict_types=1);
use Cake\Core\Configure;

echo $this->element('highlightNavi', ['main' => 'TERMINE']);
$this->element('addScript', ['script' =>
    JS_NAMESPACE.".Helper.initEventAllForm('body.events.all #list-search-form');".
    JS_NAMESPACE.".Helper.bindTooltip('body.events.all #category-icons a');
"]);
?>

<div class="left">

    <div class="top">
        <h1>Gesamtansicht Termine</h1>

        <?php
            echo $this->element('listSearchForm', [
                'baseUrl' => '/termine',
                'keyword' => $keyword,
                'categories' => $this->request->getQuery('categories'),
                'resetButton' => (($keyword != '' || count($selectedCategories) > 0 || $provinceId > 0 || $isOnlineEvent) ? true : false),
                'label' => 'Suche nach Initiativen, PLZ und Orten',
                'useTimeRange' => true,
                'showIsOnlineEventCheckbox' => true,
                'isOnlineEvent' => $isOnlineEvent,
            ]);
        ?>

        <div class="sc"></div>
        <div id="category-icons">
            <?php
                foreach($preparedCategories as $category) {
                    echo '<a href="'.$category['href'].'"
                             title="'.$category['name'].'"
                             id="category-'.$category['id'].'"
                             class="lstCat skill_icon '.$category['icon'].' '.$category['class'] .'">
                    </a>';

                }
            ?>
            <a class="button" href="<?php echo $resetCategoriesUrl; ?>" ><?php echo __('Select all'); ?></a>
       </div>

   <?php echo $this->Form->end(); ?>

    <?php
    $paginationParams = [
        'objectNameSingular' => 'Termin',
        'objectNamePlural' => 'Termine',
        'objectNameSingularDativ' => 'Termin',
        'objectNamePluralDativ' => 'Terminen',
        'allCount' => $allEventsCount
    ];
    
    echo $this->element('paginationSearch', $paginationParams);

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

            echo '<a class="title" href="'.$this->Html->urlWorkshopDetail($event->directurl).'#datum">';
            if ($event->is_online_event) {
                echo '<span class="is-online-event">[ONLINE]</span> ';
            }
            echo $event->datumstart->i18nFormat(Configure::read('DateFormat.de.DateLong2'));
            echo ' // ' . $event->workshop->name.' // ' . ' ' . $event->ort;
            echo '</a>';
            echo '<div class="text-wrapper">';
            echo '<p>Der Termin beginnt um <b>' . $event->uhrzeitstart->i18nFormat(Configure::read('DateFormat.de.TimeShort')) .'</b> und endet um <b>' . $event->uhrzeitend->i18nFormat(Configure::read('DateFormat.de.TimeShort')).' Uhr</b>.</p>';
            echo '</div>';

            foreach($preparedCategories as $preparedCategory) {
                $categoryClass = 'not-selected';
                if (!is_null($event->event_categories)) {
                    foreach($event->event_categories as $category) {
                        if ($category->category_id == $preparedCategory['id']) {
                            $categoryClass = 'selected';
                        }
                    }
                }
                echo '<div
                         title="'.$preparedCategory['name'].'"
                         class="lstCatNew skill_icon small '.$preparedCategory['icon'].' '.$categoryClass .'">
                    </div>';
            }

        echo '</div>';

    }
    if (count($events) > 0) {
        echo $this->element('pagination', ['urlOptions' => $urlOptions]);
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