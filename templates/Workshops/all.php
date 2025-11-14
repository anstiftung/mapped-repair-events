<?php
declare(strict_types=1);

use Cake\Utility\Text;
use App\Model\Entity\Event;

echo $this->element('highlightNavi', ['main' => 'ORTE']);
$this->element('addScript', ['script' =>
    JS_NAMESPACE.".Helper.initWorkshopAllForm('body.workshops.all #list-search-form');"
]);
?>

<div class="left">

    <div class="top">
        <h1>Gesamtübersicht Initiativen</h1>

        <?php
            echo $this->element('listSearchForm', [
                'baseUrl' => '/orte',
                'keyword' => $keyword,
                'provinces' => $provinces,
                'resetButton' => $keyword != '' || $provinceId > 0 ? true : false,
                'label' => 'Suche nach Initiativen, PLZ und Orten',
                'useTimeRange' => false,
                'showIsOnlineEventCheckbox' => false,
            ]);
            echo $this->Form->end();
        ?>

    <div class="sort">
      Sortieren nach:
          <?php
              echo $this->Paginator->sort('Workshops.created', 'Datum');
              echo $this->Paginator->sort('Workshops.zip', 'PLZ');
              echo $this->Paginator->sort('Workshops.city', 'Stadt');
              echo $this->Paginator->sort('Workshops.name', 'Name');
        ?>
    </div>

    <?php
    $paginationParams = [
        'objectNameSingular' => $fallbackNearbyUsed > 0 ? 'Initiative im Umkreis von '.Event::FALLBACK_RADIUS_KM.' km von "' . $keyword . '"' : 'Initiative',
        'objectNamePlural' => $fallbackNearbyUsed > 0 ? 'Initiativen im Umkreis von '.Event::FALLBACK_RADIUS_KM.' km von "' . $keyword . '"' : 'Initiativen',
    ];
    echo $this->element('paginationSearch', $paginationParams);

    echo '</div>'; // div.top
    foreach($workshops as $workshop) {

        echo '<div class="item-wrapper">';

            echo '<a title="'.$workshop->name.'" href="'.$this->Html->urlWorkshopDetail($workshop->url).'">';
                if ($workshop->image != '') {
                    echo '<img title="'.h($workshop->name).'"
                               alt="'.h($workshop->name).'" class="detail-image"
                               src="'.$this->Html->getThumbs100Image($workshop->image, 'workshops').'" />';

                } else {
                    echo '<img title="'.h($workshop->name).'"
                               alt="'.h($workshop->name).'" class="detail-image"
                               src="'.$this->Html->getThumbs100Image('rclogo-100.jpg', 'workshops').'" />';
                }
            echo '</a>';

            echo '<a class="title" href="'.$this->Html->urlWorkshopDetail($workshop->url).'">'.$workshop->name.' // ' . $workshop->street . ', ' .$workshop->zip . ' ' . $workshop->city.'</a>';
            echo '<div class="text-wrapper">';
                $textLength = 200;
                $workshopText = Text::truncate($workshop->unformatted_text, $textLength);
                if (strlen($workshopText) < strlen($workshop->unformatted_text)) {
                    if (strlen($workshop->unformatted_text) >= $textLength) {
                        $workshopText = substr($workshopText, 0, strlen($workshopText) - 4);
                        $workshopText .= '... <a href="'.$this->Html->urlWorkshopDetail($workshop->url).'">weiterlesen</a>';
                        $workshopText .= '</p>';
                    }
                }
                echo $workshopText;
            echo '</div>';

        echo '</div>';

    }
    if (count($workshops) > 0) {
        echo $this->element('paginationSearch', $paginationParams);
    }
?>

</div> <?php // left column ends here ?>

<?php if (!$this->request->getSession()->read('isMobile')) { ?>

    <div class="right">

        <?php
            $jqueryString = "var map = new ".JS_NAMESPACE.".Map(".json_encode($workshopsForMap).");";
            $jqueryString .= "map.initMarkers();";
            $jqueryString .= "map.setMapAsFixed(($('#header').height() + 8));";
            $this->element('addScript', ['script' => $jqueryString]);
        ?>

        <div id="mapContainer">
            <div id="map"></div>
        </div>


    </div><?php // right column ends here ?>

<?php } ?>