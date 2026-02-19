<?php
declare(strict_types=1);

namespace App\Controller;

use stdClass;
use Cake\Http\Response;
use Cake\Core\Configure;
use InvalidArgumentException;
use App\Model\Entity\Province;
use App\Model\Entity\Workshop;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;

class WidgetsController extends AppController
{

    public function beforeFilter(EventInterface $event): void
    {

        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated([
            'events',
            'map',
            'mapWithFundings',
            'integration',
            'statisticsGlobal',
            'statisticsWorkshop',
            'statisticsCountsGlobal',
            'statisticsCountsWorkshop',
        ]);

    }

    public function integration(): void
    {
        $this->set('metaTags', ['title' => 'Widgets Integration']);
    }

    public function statisticsCountsGlobal(): Response
    {
        $this->parseStatisticsGlobalParams();
        $this->parseStatisticsParams();
        $this->viewBuilder()->setLayout('widget');

        $this->set('assetNamespace', 'statistics-counts');
        $this->set('useJs', false);

        /** @var \App\Model\Table\InfoSheetsTable */
        $infoSheetsTable = $this->getTableLocator()->get('InfoSheets');
        $dateFrom = '2010-01-01';
        $dateTo = date('Y') . '-12-31';
        $city = $this->viewBuilder()->getVar('city');
        $province = $this->viewBuilder()->getVar('province');
        $dataRepaired = $infoSheetsTable->getRepaired($dateFrom, $dateTo, $city, $province);
        $dataRepairable = $infoSheetsTable->getRepairable($dateFrom, $dateTo, $city, $province);
        $dataNotRepaired = $infoSheetsTable->getNotRepaired($dateFrom, $dateTo, $city, $province);
        $this->set('dataRepaired', $dataRepaired);
        $this->set('dataRepairable', $dataRepairable);
        $this->set('dataNotRepaired', $dataNotRepaired);
        $this->set('showWorkshopName', false);

        return $this->render('statisticsCountsWorkshop');
    }

    public function statisticsCountsWorkshop(int $workshopUid): void
    {
        $this->parseStatisticsWorkshopParams();
        $this->parseStatisticsParams();
        $this->viewBuilder()->setLayout('widget');

        $this->set('assetNamespace', 'statistics-counts');
        $this->set('useJs', false);

        /** @var \App\Model\Table\WorkshopsTable */
        $workshopsTable = $this->getTableLocator()->get('Workshops');
        $workshop = $workshopsTable->get($workshopUid);
        $this->set('workshop', $workshop);

        /** @var \App\Model\Table\InfoSheetsTable */
        $infoSheetsTable = $this->getTableLocator()->get('InfoSheets');
        $dataRepaired = $infoSheetsTable->getRepairedByWorkshopUid($workshopUid, null, null);
        $dataRepairable = $infoSheetsTable->getRepairableByWorkshopUid($workshopUid, null, null);
        $dataNotRepaired = $infoSheetsTable->getNotRepairedByWorkshopUid($workshopUid, null, null);
        $this->set('dataRepaired', $dataRepaired);
        $this->set('dataRepairable', $dataRepairable);
        $this->set('dataNotRepaired', $dataNotRepaired);
        $this->set('showName', false);
    }

    public function events(): void
    {

        $this->viewBuilder()->setLayout('widget');

        $this->set('assetNamespace', 'events');
        $this->set('useJs', true);

        $workshopUid = (int) h($this->request->getQuery('id', 0));
        if ($workshopUid != $this->request->getQuery('id')) {
            throw new InvalidArgumentException();
        }

        /** @var \App\Model\Table\WorkshopsTable */
        $workshopsTable = $this->getTableLocator()->get('Workshops');
        $workshopsTable->get($workshopUid);

        $this->set('workshopUid', $workshopUid);

        $num = h($this->request->getQuery('num')) ?? 1;
        $num = (int) $num;
        $this->set('num', $num);

        $trunc = h($this->request->getQuery('trunc')) ?? 300;
        $trunc = (int) $trunc;
        $this->set('trunc', $trunc);

    }

    public function mapWithFundings(): Response
    {
        $this->map();
        $this->set('osmMethod', 'search-with-fundings');
        return $this->render('map');
    }

    public function map(): void
    {

        $this->set('osmMethod', 'search');

        $this->viewBuilder()->setLayout('widget');

        $this->set('assetNamespace', 'map');
        $this->set('useJs', true);

        $customZoomLevel = 0;
        $lat = '';
        $lng = '';
        $buttonColor = '';
        $buttonBgColor = '';
        $searchButtonColor = '';
        $searchButtonBgColor = '';
        $buttonMouseoverColor = '';
        $buttonMouseoverBgColor = '';
        $clusterOuterColor = '';
        $clusterInnerColor = '';
        $clusterFontColor = '';
        $markerSrc = '';
        $foundMarkerSrc = '';

        if (isset($_GET['lat'])) {
            $lat = (float) $_GET['lat'];
        }
        if (isset($_GET['lng'])) {
            $lng = (float) $_GET['lng'];
        }

        // all three variables need to be set to make zoom work properly
        // only setting zoom level it is not possible
        $customCenterCoordinates = new stdClass();
        if ($lat != 0 && is_double($lat) && $lng != 0 && is_double($lng)) {
            $customCenterCoordinates->x = $lat;
            $customCenterCoordinates->y = $lng;
            if (isset($_GET['zoomLevel'])) {
                $customZoomLevel = $_GET['zoomLevel'];
            }
        }

        $this->set('customCenterCoordinates', $customCenterCoordinates);
        $this->set('customZoomLevel', $customZoomLevel);

        if (isset($_GET['buttonColor'])) {
            $buttonColor = $this->validateHtmlColor($_GET['buttonColor']);
            if (!$buttonColor) echo 'Der Wert für den Parameter "buttonColor" ist nicht gültig. Bitte verwende eine gültige HTML-Farbangabe oder einen Hex-Code (ohne #).';
        }
        if (isset($_GET['buttonBgColor'])) {
            $buttonBgColor = $this->validateHtmlColor($_GET['buttonBgColor']);
            if (!$buttonBgColor) echo 'Der Wert für den Parameter "buttonBgColor" ist nicht gültig. Bitte verwende eine gültige HTML-Farbangabe oder einen Hex-Code (ohne #).';
        }
        if (isset($_GET['searchButtonColor'])) {
            $searchButtonColor = $this->validateHtmlColor($_GET['searchButtonColor']);
            if (!$searchButtonColor) echo 'Der Wert für den Parameter "$searchButtonColor" ist nicht gültig. Bitte verwende eine gültige HTML-Farbangabe oder einen Hex-Code (ohne #).';
        }
        if (isset($_GET['searchButtonBgColor'])) {
            $searchButtonBgColor = $this->validateHtmlColor($_GET['searchButtonBgColor']);
            if (!$searchButtonBgColor) echo 'Der Wert für den Parameter "$searchButtonBgColor" ist nicht gültig. Bitte verwende eine gültige HTML-Farbangabe oder einen Hex-Code (ohne #).';
        }
        if (isset($_GET['buttonMouseoverColor'])) {
            $buttonMouseoverColor = $this->validateHtmlColor($_GET['buttonMouseoverColor']);
            if (!$buttonMouseoverColor) echo 'Der Wert für den Parameter "$buttonMouseoverColor" ist nicht gültig. Bitte verwende eine gültige HTML-Farbangabe oder einen Hex-Code (ohne #).';
        }
        if (isset($_GET['buttonMouseoverBgColor'])) {
            $buttonMouseoverBgColor = $this->validateHtmlColor($_GET['buttonMouseoverBgColor']);
            if (!$buttonMouseoverBgColor) echo 'Der Wert für den Parameter "$buttonMouseoverBgColor" ist nicht gültig. Bitte verwende eine gültige HTML-Farbangabe oder einen Hex-Code (ohne #).';
        }
        if (isset($_GET['clusterOuterColor'])) {
            $clusterOuterColor = $this->validateHtmlColor($_GET['clusterOuterColor']);
            if (!$clusterOuterColor) echo 'Der Wert für den Parameter "$clusterOuterColor" ist nicht gültig. Bitte verwende eine gültige HTML-Farbangabe oder einen Hex-Code (ohne #).';
        }
        if (isset($_GET['clusterInnerColor'])) {
            $clusterInnerColor = $this->validateHtmlColor($_GET['clusterInnerColor']);
            if (!$clusterInnerColor) echo 'Der Wert für den Parameter "$clusterInnerColor" ist nicht gültig. Bitte verwende eine gültige HTML-Farbangabe oder einen Hex-Code (ohne #).';
        }
        if (isset($_GET['clusterFontColor'])) {
            $clusterFontColor = $this->validateHtmlColor($_GET['clusterFontColor']);
            if (!$clusterFontColor) echo 'Der Wert für den Parameter "$clusterFontColor" ist nicht gültig. Bitte verwende eine gültige HTML-Farbangabe oder einen Hex-Code (ohne #).';
        }

        $this->set('buttonColor', $buttonColor);
        $this->set('buttonBgColor', $buttonBgColor);
        $this->set('searchButtonColor', $searchButtonColor);
        $this->set('searchButtonBgColor', $searchButtonBgColor);
        $this->set('buttonMouseoverColor', $buttonMouseoverColor);
        $this->set('buttonMouseoverBgColor', $buttonMouseoverBgColor);
        $this->set('clusterOuterColor', $clusterOuterColor);
        $this->set('clusterInnerColor', $clusterInnerColor);
        $this->set('clusterFontColor', $clusterFontColor);

        if (isset($_GET['markerSrc'])) {
            $markerSrc = $_GET['markerSrc'];
        }
        $this->set('markerSrc', $markerSrc);

        if (isset($_GET['foundMarkerSrc'])) {
            $foundMarkerSrc = $_GET['foundMarkerSrc'];
        }
        $this->set('foundMarkerSrc', $foundMarkerSrc);

    }

    public function statisticsGlobal(): void
    {
        $this->viewBuilder()->setLayout('widget');
        $this->parseStatisticsParams();
        $this->parseStatisticsGlobalParams();

        $this->set('assetNamespace', 'statistics');
        $this->set('useJs', true);

        $metaTags = ['title' => 'Globale Statistik'];
        $this->set('metaTags', $metaTags);
        $showBarChart = $this->viewBuilder()->getVar('showBarChart');
        if ($showBarChart) {
            $this->statisticsGlobalCategories(
                $this->viewBuilder()->getVar('month'),
                $this->viewBuilder()->getVar('year'),
                $this->viewBuilder()->getVar('backgroundColorOk'),
                $this->viewBuilder()->getVar('backgroundColorRepairable'),
                $this->viewBuilder()->getVar('backgroundColorNotOk'),
                $this->viewBuilder()->getVar('borderColorOk'),
                $this->viewBuilder()->getVar('borderColorRepairable'),
                $this->viewBuilder()->getVar('borderColorNotOk'),
                $this->viewBuilder()->getVar('dataSource'),
                $this->viewBuilder()->getVar('city'),
                $this->viewBuilder()->getVar('province'),
            );

            /** @var \App\Model\Table\InfoSheetsTable */
            $infoSheetsTable = $this->getTableLocator()->get('InfoSheets');
            $dates = $this->getDateFromByMonthAndYear($this->viewBuilder()->getVar('month'), $this->viewBuilder()->getVar('year'));
            $workshopCount = $infoSheetsTable->getWorkshopCountWithInfoSheets($dates['dateFrom'], $dates['dateTo'], $this->viewBuilder()->getVar('city'), $this->viewBuilder()->getVar('province'));
            $this->set('workshopCount', $workshopCount);
        }
        $showDonutChart = $this->viewBuilder()->getVar('showDonutChart');
        if ($showDonutChart) {
            $this->statisticsGlobalRepaired(
                $this->viewBuilder()->getVar('month'),
                $this->viewBuilder()->getVar('year'),
                $this->viewBuilder()->getVar('backgroundColorOk'),
                $this->viewBuilder()->getVar('backgroundColorRepairable'),
                $this->viewBuilder()->getVar('backgroundColorNotOk'),
                $this->viewBuilder()->getVar('borderColorOk'),
                $this->viewBuilder()->getVar('borderColorRepairable'),
                $this->viewBuilder()->getVar('borderColorNotOk'),
                $this->viewBuilder()->getVar('city'),
                $this->viewBuilder()->getVar('province'),
            );
        }

    }

    public function statisticsWorkshop(int $workshopUid): void
    {

        $this->parseStatisticsParams();
        $this->parseStatisticsWorkshopParams();

        $this->set('assetNamespace', 'statistics');
        $this->set('useJs', true);

        $this->viewBuilder()->setLayout('widget');

        /** @var \App\Model\Table\WorkshopsTable */
        $workshopsTable = $this->getTableLocator()->get('Workshops');
        $workshop = $workshopsTable->find('all', conditions: [
            'Workshops.uid' => $workshopUid,
            'Workshops.show_statistics > ' => Workshop::STATISTICS_DISABLED,
        ])->first();
        if (empty($workshop)) {
            throw new NotFoundException;
        }

        $metaTags = ['title' => 'Statistik für ' . $workshop->name];
        $this->set('metaTags', $metaTags);

        $showBarChart = $this->viewBuilder()->getVar('showBarChart');
        if ($showBarChart) {
            $this->statisticsWorkshopCategories(
                $workshop->uid,
                $this->viewBuilder()->getVar('dateFrom'),
                $this->viewBuilder()->getVar('dateTo'),
                $this->viewBuilder()->getVar('backgroundColorOk'),
                $this->viewBuilder()->getVar('backgroundColorRepairable'),
                $this->viewBuilder()->getVar('backgroundColorNotOk'),
                $this->viewBuilder()->getVar('borderColorOk'),
                $this->viewBuilder()->getVar('borderColorRepairable'),
                $this->viewBuilder()->getVar('borderColorNotOk')
            );
        }
        $showDonutChart = $this->viewBuilder()->getVar('showDonutChart');
        if ($showDonutChart) {
            $this->statisticsWorkshopRepaired(
                $workshop->uid,
                $this->viewBuilder()->getVar('dateFrom'),
                $this->viewBuilder()->getVar('dateTo'),
                $this->viewBuilder()->getVar('backgroundColorOk'),
                $this->viewBuilder()->getVar('backgroundColorRepairable'),
                $this->viewBuilder()->getVar('backgroundColorNotOk'),
                $this->viewBuilder()->getVar('borderColorOk'),
                $this->viewBuilder()->getVar('borderColorRepairable'),
                $this->viewBuilder()->getVar('borderColorNotOk')
            );
        }

        $this->set('workshop', $workshop);

    }

    private function statisticsWorkshopCategories(
        int $workshopUid,
        string $dateFrom,
        string $dateTo,
        string $backgroundColorOk,
        string $backgroundColorRepairable,
        string $backgroundColorNotOk,
        string $borderColorOk,
        string $borderColorRepairable,
        string $borderColorNotOk,
        ): void
    {

        /** @var \App\Model\Table\InfoSheetsTable */
        $infoSheetsTable = $this->getTableLocator()->get('InfoSheets');
        /** @var \App\Model\Table\CategoriesTable */
        $categoriesTable = $this->getTableLocator()->get('Categories');
        $categoriesForStatistics = $categoriesTable->getMainCategoriesForFrontend()->toArray();

        $categoriesLabels = [];
        $categoriesDatasets = [];
        $categoriesDataRepaired = [];
        $categoriesDataRepairable = [];
        $categoriesDataNotRepaired = [];
        $carbonFootprintSum = 0;
        foreach($categoriesForStatistics as &$c) {
            $repaired = $infoSheetsTable->getRepairedByMainCategoryId($workshopUid, $c['id'], $dateFrom, $dateTo);
            $repairable = $infoSheetsTable->getRepairableByMainCategoryId($workshopUid, $c['id'], $dateFrom, $dateTo);
            $notRepaired = $infoSheetsTable->getNotRepairedByMainCategoryId($workshopUid, $c['id'], $dateFrom, $dateTo);
            $carbonFootprint = $categoriesTable->getCarbonFootprintByParentCategoryId($c->id);
            $carbonFootprintSum += $categoriesTable->calculateCarbonFootprint($repaired, $carbonFootprint);
            if ($repaired == 0 && $repairable == 0 && $notRepaired == 0) {
                unset($c);
                continue;
            }
            $categoriesLabels[] = $c->name;
            $categoriesDataRepaired[] = $repaired;
            $categoriesDataRepairable[] = $repairable;
            $categoriesDataNotRepaired[] = $notRepaired;
        }
        $categoriesDataset = [
            'backgroundColor' => $backgroundColorOk,
            'borderColor' => $borderColorOk,
            'borderWidth' => 1,
            'data' => $categoriesDataRepaired,
            'label' => ['Repariert (' . array_sum($categoriesDataRepaired)  . ')']
        ];
        $categoriesDatasets[] = $categoriesDataset;

        $categoriesDataset = [
            'backgroundColor' => $backgroundColorRepairable,
            'borderColor' => $borderColorRepairable,
            'borderWidth' => 1,
            'data' => $categoriesDataRepairable,
            'label' => ['Reparabel (' . array_sum($categoriesDataRepairable)  . ')']
        ];
        $categoriesDatasets[] = $categoriesDataset;

        $categoriesDataset = [
            'backgroundColor' => $backgroundColorNotOk,
            'borderColor' => $borderColorNotOk,
            'borderWidth' => 1,
            'data' => $categoriesDataNotRepaired,
            'label' => ['Nicht repariert (' . array_sum($categoriesDataNotRepaired)  . ')']
        ];
        $categoriesDatasets[] = $categoriesDataset;

        $this->set('statisticsCategoriesData', [
            'labels' => $categoriesLabels,
            'datasets' => $categoriesDatasets
        ]);

        $this->setBarChartHasData($this->viewBuilder()->getVar('statisticsCategoriesData'));
        $this->set('carbonFootprintSum', $carbonFootprintSum);

    }

    private function statisticsWorkshopRepaired(
        int $workshopUid,
        string $dateFrom,
        string $dateTo,
        string $backgroundColorOk,
        string $backgroundColorRepairable,
        string $backgroundColorNotOk,
        string $borderColorOk,
        string $borderColorRepairable,
        string $borderColorNotOk,
        ): void
    {

        /** @var \App\Model\Table\InfoSheetsTable */
        $infoSheetsTable = $this->getTableLocator()->get('InfoSheets');
        $dataRepaired = $infoSheetsTable->getRepairedByWorkshopUid($workshopUid, $dateFrom, $dateTo);
        $dataRepairable = $infoSheetsTable->getRepairableByWorkshopUid($workshopUid, $dateFrom, $dateTo);
        $dataNotRepaired = $infoSheetsTable->getNotRepairedByWorkshopUid($workshopUid, $dateFrom, $dateTo);
        $this->set('statisticsRepairedData', [
            'backgroundColor' => [$backgroundColorOk, $backgroundColorRepairable, $backgroundColorNotOk],
            'borderColor' => [$borderColorOk, $borderColorRepairable, $borderColorNotOk],
            'borderWidth' => 1,
            'labels' => [
                'Repariert (' . $dataRepaired . ')',
                'Reparabel (' . $dataRepairable . ')',
                'Nicht repariert (' . $dataNotRepaired . ')',
            ],
            'data' => [
                $dataRepaired,
                $dataRepairable,
                $dataNotRepaired,
            ]
        ]);

        $this->setDonutChartHasData($this->viewBuilder()->getVar('statisticsRepairedData'));

    }

    private function parseStatisticsWorkshopParams(): void
    {
        $dateFrom = '01.01.2010';
        if (!empty($this->request->getQuery('dateTo') && Configure::read('AppConfig.timeHelper')->validateDate($this->request->getQuery('dateFrom')))) {
            $dateFrom = h($this->request->getQuery('dateFrom'));
        }
        $this->set('dateFrom', $dateFrom);

        $dateTo = date('d.m.Y');
        if (!empty($this->request->getQuery('dateTo') && Configure::read('AppConfig.timeHelper')->validateDate($this->request->getQuery('dateTo')))) {
            $dateTo = h($this->request->getQuery('dateTo'));
        }
        $this->set('dateTo', $dateTo);

        $showWorkshopName = true;
        if (in_array('showWorkshopName', array_keys($this->request->getQueryParams()))) {
            $showWorkshopName = h($this->request->getQuery('showWorkshopName')) == 1 ? true : false;
        }
        $this->set('showWorkshopName', $showWorkshopName);

        $showCarbonFootprint = true;
        if (in_array('showCarbonFootprint', array_keys($this->request->getQueryParams()))) {
            $showCarbonFootprint = h($this->request->getQuery('showCarbonFootprint')) == 1 ? true : false;
        }
        $this->set('showCarbonFootprint', $showCarbonFootprint);

    }

    private function parseStatisticsGlobalParams(): void
    {

        $dataSources = [
            'all' => 'Datenquelle: alle',
            'third-party-name' => Configure::read('AppConfig.thirdPartyStatisticsProviderName'),
            'platform' => Configure::read('AppConfig.platformName'),
        ];
        $this->set('dataSources', $dataSources);

        $defaultDataSource = 'all';
        if (!empty($this->request->getQuery('defaultDataSource'))) {
            if (in_array($this->request->getQuery('defaultDataSource'), array_keys($dataSources))) {
                $defaultDataSource = h($this->request->getQuery('defaultDataSource'));
            }
        }
        $this->set('defaultDataSource', $defaultDataSource);

        $dataSource = $defaultDataSource;
        if (!empty($this->request->getQuery('dataSource'))) {
            if (in_array($this->request->getQuery('dataSource'), array_keys($dataSources))) {
                $dataSource = h($this->request->getQuery('dataSource'));
            }
        }

        $month = '';
        if (!empty($this->request->getQuery('month'))) {
            $month = h($this->request->getQuery('month'));
        }

        $year = '';
        if (!empty($this->request->getQuery('year'))) {
            $year = h($this->request->getQuery('year'));
        }

        if ($month != '' && $year == '') {
            $month = '';
        }

        $this->set('year', $year);
        $this->set('month', $month);

        $city = h($this->request->getQuery('city', ''));
        $this->set('city', $city);

        $province = h($this->request->getQuery('province', ''));
        $provincesTable = $this->getTableLocator()->get('Provinces');
        $provinceEntity = $provincesTable->findByName($province);
        if ($province != '' && !$provinceEntity instanceof Province) {
            throw new NotFoundException('Das angegebene Bundesland / Kanton "' . $province . '" wurde nicht gefunden.');
        }
        $this->set('province', $provinceEntity);

        if ($city != '' || $province != '') {
            $dataSource = 'platform';
        }
        $this->set('dataSource', $dataSource);

        $showName = (bool) $this->request->getQuery('showName', true);
        $this->set('showName', $showName);

    }

    private function parseStatisticsParams(): void
    {

        $showDonutChart = true;
        if (in_array('showDonutChart', array_keys($this->request->getQueryParams()))) {
            $showDonutChart = h($this->request->getQuery('showDonutChart')) == 1 ? true : false;
        }
        $this->set('showDonutChart', $showDonutChart);

        $showBarChart = true;
        if (in_array('showBarChart', array_keys($this->request->getQueryParams()))) {
            $showBarChart = h($this->request->getQuery('showBarChart')) == 1 ? true : false;
        }
        $this->set('showBarChart', $showBarChart);

        $backgroundColorOk = Configure::read('AppConfig.widgetHelper')->getDefaultChartBackgroundColorOk();
        if (!empty($this->request->getQuery('backgroundColorOk')) && $this->validateHtmlColor($this->request->getQuery('backgroundColorOk'))) {
            $backgroundColorOk = $this->validateHtmlColor(h($this->request->getQuery('backgroundColorOk')));
        }
        $this->set('backgroundColorOk', $backgroundColorOk);

        $backgroundColorRepairable = Configure::read('AppConfig.widgetHelper')->getDefaultChartBackgroundColorRepairable();
        if (!empty($this->request->getQuery('backgroundColorRepairable')) && $this->validateHtmlColor($this->request->getQuery('backgroundColorRepairable'))) {
            $backgroundColorRepairable = $this->validateHtmlColor(h($this->request->getQuery('backgroundColorRepairable')));
        }
        $this->set('backgroundColorRepairable', $backgroundColorRepairable);

        $backgroundColorNotOk = Configure::read('AppConfig.widgetHelper')->getDefaultChartBackgroundColorNotOk();
        if (!empty($this->request->getQuery('backgroundColorNotOk')) && $this->validateHtmlColor($this->request->getQuery('backgroundColorNotOk'))) {
            $backgroundColorNotOk = $this->validateHtmlColor(h($this->request->getQuery('backgroundColorNotOk')));
        }
        $this->set('backgroundColorNotOk', $backgroundColorNotOk);

        $borderColorOk = Configure::read('AppConfig.widgetHelper')->getDefaultChartBorderColorOk();
        if (!empty($this->request->getQuery('borderColorOk')) && $this->validateHtmlColor($this->request->getQuery('borderColorOk'))) {
            $borderColorOk = $this->validateHtmlColor(h($this->request->getQuery('borderColorOk')));
        }
        $this->set('borderColorOk', $borderColorOk);

        $borderColorNotOk = Configure::read('AppConfig.widgetHelper')->getDefaultChartBorderColorNotOk();
        if (!empty($this->request->getQuery('borderColorNotOk')) && $this->validateHtmlColor($this->request->getQuery('borderColorNotOk'))) {
            $borderColorNotOk = $this->validateHtmlColor(h($this->request->getQuery('borderColorNotOk')));
        }
        $this->set('borderColorNotOk', $borderColorNotOk);

        $borderColorRepairable = Configure::read('AppConfig.widgetHelper')->getDefaultChartBorderColorRepairable();
        if (!empty($this->request->getQuery('borderColorRepairable')) && $this->validateHtmlColor($this->request->getQuery('borderColorRepairable'))) {
            $borderColorRepairable = $this->validateHtmlColor(h($this->request->getQuery('borderColorRepairable')));
        }
        $this->set('borderColorRepairable', $borderColorRepairable);

    }

    /**
     * @param array<string> $month
     * @param array<string> $year
     * @return array<string, string>
     */
    private function getDateFromByMonthAndYear(string|array $month, string|array $year): array
    {
        $yearTo = $year;
        if (is_array($year) && isset($year['year'])) {
            $yearTo = $year = $year['year'];
        }
        if ($year == '') {
            $year = '2010';
            $yearTo = date('Y');
        }
        $monthTo = $month;
        if (is_array($month) && isset($month['month'])) {
            $monthTo = $month = $month['month'];
        }
        if ($month == '') {
            $month = '01';
            $monthTo = '12';
        }
        $dateFrom = '01.' . $month . '.'. $year;
        $dateTo = Configure::read('AppConfig.timeHelper')->getLastDayOfGivenMonth($yearTo.'-'.$monthTo) . '.' . $monthTo . '.' . $yearTo;

        return [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ];
    }

    private function statisticsGlobalCategories(
        string $month,
        string $year,
        string $backgroundColorOk,
        string $backgroundColorRepairable,
        string $backgroundColorNotOk,
        string $borderColorOk,
        string $borderColorRepairable,
        string $borderColorNotOk,
        string $dataSource,
        string $city,
        ?Province $province,
        ): void
    {

        $dates = $this->getDateFromByMonthAndYear($month, $year);
        $dateFrom = $dates['dateFrom'];
        $dateTo = $dates['dateTo'];

        $includeThirdPartyStatistics = in_array($dataSource, ['all', 'third-party-name']);
        $includeInfoSheetStatistics = in_array($dataSource, ['all', 'platform']);

        /** @var \App\Model\Table\CategoriesTable */
        $categoriesTable = $this->getTableLocator()->get('Categories');
        $categoriesForStatistics = $categoriesTable->getCategoriesForStatisticsGlobal();

        $categoriesIds = [];
        $categoriesLabels = [];
        $categoriesDatasets = [];
        $categoriesDataRepaired = [];
        $categoriesDataRepairable = [];
        $categoriesDataNotRepaired = [];
        $carbonFootprintSum = 0;
        $materialFootprintSum = 0;

        foreach($categoriesForStatistics as $category) {
            $categoriesIds[] = $category['id'];
            $categoriesLabels[] = $category ['name'];
            $categoriesCarbonFootprint[] = $category['carbon_footprint'];
            $categoriesMaterialFootprint[] = $category['material_footprint'];
            $categoriesDataRepaired[] = 0;
            $categoriesDataRepairable[] = 0;
            $categoriesDataNotRepaired[] = 0;
        }

        if ($includeInfoSheetStatistics) {

            /** @var \App\Model\Table\InfoSheetsTable */
            $infoSheetsTable = $this->getTableLocator()->get('InfoSheets');
            foreach($categoriesIds as $index => $mainCategoryId) {
                $repaired = $infoSheetsTable->getRepairedGlobalByMainCategoryId($mainCategoryId, $dateFrom, $dateTo, $city, $province);
                $carbonFootprint = $categoriesTable->getCarbonFootprintByParentCategoryId($mainCategoryId);
                $carbonFootprintSum += $categoriesTable->calculateCarbonFootprint($repaired, $carbonFootprint);
                $materialFootprint = $categoriesTable->getMaterialFootprintByParentCategoryId($mainCategoryId);
                $materialFootprintSum += $categoriesTable->calculateMaterialFootprint($repaired, $materialFootprint);
                $notRepaired = $infoSheetsTable->getNotRepairedGlobalByMainCategoryId($mainCategoryId, $dateFrom, $dateTo, $city, $province);
                $repairable = $infoSheetsTable->getRepairableGlobalByMainCategoryId($mainCategoryId, $dateFrom, $dateTo, $city, $province);
                $categoriesDataRepaired[$index] += $repaired;
                $categoriesDataRepairable[$index] += $repairable;
                $categoriesDataNotRepaired[$index] += $notRepaired;
            }

        }

        if ($includeThirdPartyStatistics) {

            /** @var \App\Model\Table\ThirdPartyStatisticsTable */
            $thirdPartyStatisticsTable = $this->getTableLocator()->get('ThirdPartyStatistics');
            $thirdPartySums = $thirdPartyStatisticsTable->getSumsByDate($dateFrom, $dateTo);
            $thirdPartyPreparedSums = $thirdPartyStatisticsTable->sumUpForMainCategory($thirdPartySums);
            $thirdPartyPreparedSums = $thirdPartyStatisticsTable->bindCategoryDataToSums($thirdPartyPreparedSums);

            foreach($categoriesIds as $index => $categoryId) {
                foreach($thirdPartyPreparedSums as $thirdPartyPreparedSum) {
                    /* @phpstan-ignore-next-line */
                    $carbonFootprintSum += $categoriesTable->calculateCarbonFootprint((float) $thirdPartyPreparedSum['repaired'], (float) $categoriesCarbonFootprint[$index]);
                    /* @phpstan-ignore-next-line */
                    $materialFootprintSum += $categoriesTable->calculateMaterialFootprint((float) $thirdPartyPreparedSum['repaired'], (float) $categoriesMaterialFootprint[$index]);
                    if ($categoryId == $thirdPartyPreparedSum['id']) {
                        @$categoriesDataRepaired[$index] += $thirdPartyPreparedSum['repaired'];
                        continue;
                    }
                }
            }

        }

        // clean categories with no data
        foreach($categoriesLabels as $index => $categoryLabel) {
            if ($categoriesDataRepaired[$index] == 0 &&
                $categoriesDataRepairable[$index] == 0 &&
                $categoriesDataNotRepaired[$index] == 0) {
                    unset($categoriesLabels[$index]);
                    unset($categoriesDataRepaired[$index]);
                    unset($categoriesDataRepairable[$index]);
                    unset($categoriesDataNotRepaired[$index]);
            }
        }
        $categoriesLabels = array_values($categoriesLabels);
        $categoriesDataRepaired = array_values($categoriesDataRepaired);
        $categoriesDataRepairable = array_values($categoriesDataRepairable);
        $categoriesDataNotRepaired = array_values($categoriesDataNotRepaired);

        $categoriesDataset = [
            'backgroundColor' => $backgroundColorOk,
            'borderColor' => $borderColorOk,
            'borderWidth' => 1,
            'data' => $categoriesDataRepaired,
            'label' => ['Repariert (' . Configure::read('AppConfig.numberHelper')->format(array_sum($categoriesDataRepaired)) . ')']
        ];
        $categoriesDatasets[] = $categoriesDataset;

        $categoriesDataset = [
            'backgroundColor' => $backgroundColorRepairable,
            'borderColor' => $borderColorRepairable,
            'borderWidth' => 1,
            'data' => $categoriesDataRepairable,
            'label' => ['Reparabel (' . Configure::read('AppConfig.numberHelper')->format(array_sum($categoriesDataRepairable)) . ')']
        ];
        $categoriesDatasets[] = $categoriesDataset;

        $categoriesDataset = [
            'backgroundColor' => $backgroundColorNotOk,
            'borderColor' => $borderColorNotOk,
            'borderWidth' => 1,
            'data' => $categoriesDataNotRepaired,
            'label' => ['Nicht repariert (' . Configure::read('AppConfig.numberHelper')->format(array_sum($categoriesDataNotRepaired)) . ')']
        ];
        $categoriesDatasets[] = $categoriesDataset;

        $this->set('statisticsCategoriesData', [
            'labels' => $categoriesLabels,
            'datasets' => $categoriesDatasets
        ]);
        $this->set('carbonFootprintSum', $carbonFootprintSum);
        $this->set('materialFootprintSum', $materialFootprintSum);

        $this->setBarChartHasData($this->viewBuilder()->getVar('statisticsCategoriesData'));

    }

    private function statisticsGlobalRepaired(
        string $month,
        string $year,
        string $backgroundColorOk,
        string $backgroundColorRepairable,
        string $backgroundColorNotOk,
        string $borderColorOk,
        string $borderColorRepairable,
        string $borderColorNotOk,
        string $city,
        ?Province $province,
        ): void
    {

        $dates = $this->getDateFromByMonthAndYear($month, $year);
        $dateFrom = $dates['dateFrom'];
        $dateTo = $dates['dateTo'];

        /** @var \App\Model\Table\InfoSheetsTable */
        $infoSheetsTable = $this->getTableLocator()->get('InfoSheets');

        $dataRepaired = $infoSheetsTable->getRepaired($dateFrom, $dateTo, $city, $province);
        $dataRepairable = $infoSheetsTable->getRepairable($dateFrom, $dateTo, $city, $province);
        $dataNotRepaired = $infoSheetsTable->getNotRepaired($dateFrom, $dateTo, $city, $province);

        $this->set('statisticsRepairedData', [
            'backgroundColor' => [$backgroundColorOk, $backgroundColorRepairable, $backgroundColorNotOk],
            'borderColor' => [$borderColorOk, $borderColorRepairable, $borderColorNotOk],
            'borderWidth' => 1,
            'labels' => [
                'Repariert (' . Configure::read('AppConfig.numberHelper')->format($dataRepaired) . ')',
                'Reparabel (' . Configure::read('AppConfig.numberHelper')->format($dataRepairable) . ')',
                'Nicht Repariert (' . Configure::read('AppConfig.numberHelper')->format($dataNotRepaired) . ')',
            ],
            'data' => [
                $dataRepaired,
                $dataRepairable,
                $dataNotRepaired,
            ]
        ]);

    }

    /**
     * @param array<string, mixed> $statisticsData
     */
    private function setDonutChartHasData(array $statisticsData): void
    {
        $this->set('chartHasData', $statisticsData['data'][0] > 0 || $statisticsData['data'][1] > 0);
    }

    /**
     * @param array<string, mixed> $statisticsData
     */
    private function setBarChartHasData(array $statisticsData): void
    {
        $this->set('chartHasData', !empty($statisticsData['datasets'][0]['data']) || !empty($statisticsData['datasets'][1]['data']));
    }

    private function validateHtmlColor(string $color): false|string
    {

        $namedColors = ['aliceblue', 'antiquewhite', 'aqua', 'aquamarine', 'azure', 'beige', 'bisque', 'black', 'blanchedalmond', 'blue', 'blueviolet', 'brown', 'burlywood', 'cadetblue', 'chartreuse', 'chocolate', 'coral', 'cornflowerblue', 'cornsilk', 'crimson', 'cyan', 'darkblue', 'darkcyan', 'darkgoldenrod', 'darkgray', 'darkgreen', 'darkkhaki', 'darkmagenta', 'darkolivegreen', 'darkorange', 'darkorchid', 'darkred', 'darksalmon', 'darkseagreen', 'darkslateblue', 'darkslategray', 'darkturquoise', 'darkviolet', 'deeppink', 'deepskyblue', 'dimgray', 'dodgerblue', 'firebrick', 'floralwhite', 'forestgreen', 'fuchsia', 'gainsboro', 'ghostwhite', 'gold', 'goldenrod', 'gray', 'green', 'greenyellow', 'honeydew', 'hotpink', 'indianred', 'indigo', 'ivory', 'khaki', 'lavender', 'lavenderblush', 'lawngreen', 'lemonchiffon', 'lightblue', 'lightcoral', 'lightcyan', 'lightgoldenrodyellow', 'lightgreen', 'lightgrey', 'lightpink', 'lightsalmon', 'lightseagreen', 'lightskyblue', 'lightslategray', 'lightsteelblue', 'lightyellow', 'lime', 'limegreen', 'linen', 'magenta', 'maroon', 'mediumaquamarine', 'mediumblue', 'mediumorchid', 'mediumpurple', 'mediumseagreen', 'mediumslateblue', 'mediumspringgreen', 'mediumturquoise', 'mediumvioletred', 'midnightblue', 'mintcream', 'mistyrose', 'moccasin', 'navajowhite', 'navy', 'oldlace', 'olive', 'olivedrab', 'orange', 'orangered', 'orchid', 'palegoldenrod', 'palegreen', 'paleturquoise', 'palevioletred', 'papayawhip', 'peachpuff', 'peru', 'pink', 'plum', 'powderblue', 'purple', 'red', 'rosybrown', 'royalblue', 'saddlebrown', 'salmon', 'sandybrown', 'seagreen', 'seashell', 'sienna', 'silver', 'skyblue', 'slateblue', 'slategray', 'snow', 'springgreen', 'steelblue', 'tan', 'teal', 'thistle', 'tomato', 'turquoise', 'violet', 'wheat', 'white', 'whitesmoke', 'yellow', 'yellowgreen'];
        if (in_array($color, $namedColors)) {
            return $color;
        }

        if (preg_match('/^[a-f0-9]{3}|[a-f0-9]{6}$/i', $color)) {
            return '#'.$color;
        }

        if (preg_match('/^(\#[\da-f]{3}|\#[\da-f]{6}|rgba\(((\d{1,2}|1\d\d|2([0-4]\d|5[0-5]))\s*,\s*){2}((\d{1,2}|1\d\d|2([0-4]\d|5[0-5]))\s*)(,\s*(0\.\d+|1))\)|hsla\(\s*((\d{1,2}|[1-2]\d{2}|3([0-5]\d|60)))\s*,\s*((\d{1,2}|100)\s*%)\s*,\s*((\d{1,2}|100)\s*%)(,\s*(0\.\d+|1))\)|rgb\(((\d{1,2}|1\d\d|2([0-4]\d|5[0-5]))\s*,\s*){2}((\d{1,2}|1\d\d|2([0-4]\d|5[0-5]))\s*)|hsl\(\s*((\d{1,2}|[1-2]\d{2}|3([0-5]\d|60)))\s*,\s*((\d{1,2}|100)\s*%)\s*,\s*((\d{1,2}|100)\s*%)\))/i', $color)) {
            return $color;
        }

        return false;

    }

}

?>