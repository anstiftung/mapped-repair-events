<?php

namespace App\View\Helper;

use Cake\View\View;
use Cake\Core\Configure;
use Cake\View\Helper\HtmlHelper;
use Cake\Datasource\FactoryLocator;
use App\Controller\Component\StringComponent;

class MyHtmlHelper extends HtmlHelper {

    public function getHostWithoutProtocol($hostnameWithProtocol)
    {
        $parsedHostnameWithProtocol = (parse_url($hostnameWithProtocol));
        if (!empty($parsedHostnameWithProtocol['host'])) {
            return $parsedHostnameWithProtocol['host'];
        }
        return false;
    }

    public function getWorkshopStatisticTypes()
    {
        $workshop = FactoryLocator::get('Table')->get('Workshops');
        return [
            $workshop::STATISTICS_DISABLED => 'Keine Statistik im Profil anzeigen',
            $workshop::STATISTICS_SHOW_ALL => 'Gesamte Statistik anzeigen',
            $workshop::STATISTICS_SHOW_ONLY_CHART => 'Nur Zahlenstatistik im Profil anzeigen',
        ];
    }

    public function getMenuTypes()
    {
        return [
            'no-menu' => 'In keinem Menü verlinken',
            'header' => 'Header (oben)',
            'footer' => 'Footer (unten)'
        ];
    }

    public function getAdditionalBlogCategoryUrl()
    {
        return StringComponent::slugify(Configure::read('AppConfig.additionalBlogCategoryName'));
    }

    public function getHostName()
    {
        $serverName = Configure::read('AppConfig.serverName');
        $parsedServerName = parse_url($serverName)['host'];
        $parsedServerName = str_replace('www.', '', $parsedServerName);
        return $parsedServerName;
    }

    function getCarbonFootprintAsString($carbonFootprintSum)
    {

        $co2AeqPerFlightKilometer = 0.238;
        $carbonFootprintSumInFlightKilometers = $carbonFootprintSum / $co2AeqPerFlightKilometer;

        $unit = 'kg';
        $carbonFootprintSumForView = $carbonFootprintSum;
        if ($carbonFootprintSum >= 1000) {
            $carbonFootprintSumForView /= 1000;
            $unit = 't';
        }

        $distanceToSun  =   149600000;
        $distanceToMars =    55650000;
        $distanceToMoon =      380000;
        $circumferenceOfEarth = 40075;

        $humanUnderstandableComparisonFactor = 0;
        if ($carbonFootprintSumInFlightKilometers >= $circumferenceOfEarth) {
            if ($carbonFootprintSumInFlightKilometers > $distanceToSun) {
                $humanUnderstandableComparisonString = 'der mittleren Entfernung zur Sonne';
                $humanUnderstandableComparisonFactor = $carbonFootprintSumInFlightKilometers / $distanceToSun;
            }
            if ($carbonFootprintSumInFlightKilometers <= $distanceToSun) {
                $humanUnderstandableComparisonString = 'der mittleren Entfernung zum Mars';
                $humanUnderstandableComparisonFactor = $carbonFootprintSumInFlightKilometers / $distanceToMars;
            }
            if ($carbonFootprintSumInFlightKilometers <= $distanceToMars) {
                $humanUnderstandableComparisonString = 'der Entfernung zum Mond';
                $humanUnderstandableComparisonFactor = $carbonFootprintSumInFlightKilometers / $distanceToMoon;
            }
            if ($carbonFootprintSumInFlightKilometers <= 1000000) {
                $humanUnderstandableComparisonString = 'um den Äquator';
                $humanUnderstandableComparisonFactor = $carbonFootprintSumInFlightKilometers / $circumferenceOfEarth;
            }
        }

        $result = '';
        $result .= Configure::read('AppConfig.numberHelper')->precision($carbonFootprintSumForView, 0) . ' ' . $unit;
        $result .= ' CO2 bzw. ';
        $result .= $this->Number->precision($carbonFootprintSumInFlightKilometers, 0);
        $result .= ' km mit dem Flugzeug';

        if (isset($humanUnderstandableComparisonString)) {
            $result .= ', entspricht ca. ' . $this->Number->format(
                $this->roundUpToAny($humanUnderstandableComparisonFactor, 2)
            ) . 'x ' . $humanUnderstandableComparisonString;
        }

        return $result;
    }

    public function getMaterialFootprintAsString($materialFootprintSum)
    {
        $annualConsumptionPerCapitaInKg = 25800;
        $amountPersonsPerYear = round($materialFootprintSum / $annualConsumptionPerCapitaInKg, 1);

        $unit = 'kg';
        $materialFootprintSumForView = $materialFootprintSum;
        if ($materialFootprintSum >= 1000) {
            $materialFootprintSumForView /= 1000;
            $unit = 't';
        }

        $digits = 0;
        if ($amountPersonsPerYear < 10) {
            $digits = 1;
        }

        $result = '';
        $roundedAmountPersonsPerYear = round($amountPersonsPerYear, $digits);
        $result .= $this->Number->precision($materialFootprintSumForView, 0);
        $result .= ' ' . $unit . '  Boden und Gestein mussten nicht gefördert werden, das entspricht dem Jahresrohstoffverbrauch';
        $result .= ' von ca. <span class="amount-persons-per-year">'.$roundedAmountPersonsPerYear.'</span>';
        $result .= $this->Number->precision($roundedAmountPersonsPerYear, $digits) . ' Personen';
        return $result;
    }

    public function getWorkshopsCountForGlobalStatisticsString($workshopCount)
    {
        return Configure::read('AppConfig.numberHelper')->precision($workshopCount, 0) . ' ' . 'Initiativen haben mit ihren Reparaturdaten zu dieser Statistik beigetragen.';
    }

    function roundUpToAny($n,$x) {
        return round($n*$x) / $x;
    }

    function getGenders()
    {
        return [
            'f' => 'weiblich',
            'm' => 'männlich'
        ];
    }

    function generateGenericRadioButton($form, $formField)
    {
        $result = '<div class="required form-fields-checkbox-wrapper dependent-form-field '.$formField->identifier.'">'.
            '<label>'.$formField->name.'</label>'.
            $form->control('InfoSheets.' . $formField->identifier, [
                'type' => 'radio',
                'options' => $formField->preparedOptions,
                'label' => false
            ]).
            '</div>';
            return $result;

    }

    function addClassToFormInputContainer($class)
    {
        return '<div class="'.$class.' input {{type}}">{{content}}</div>';
    }

    function addClassToFormInputContainerError($class)
    {
        return '<div class="'.$class.' input {{type}}{{required}} error">{{content}}{{error}}</div>';
    }

    function getFacebookHint()
    {
        $html = '<strong>Facebook</strong>
         <p>Hier können nur Facebook-Seiten eingetragen werden, keine Personenprofile.
         Dazu ruft ihr eure Facebook-Seite im Browser auf und tragt alles, was <b>nach</b>  http://www.facebook.com/ steht in das Feld ein.
        Beispiel: NICHT https://www.facebook.com/netzwerk-reparatur-initiatven, sondern lediglich netzwerk-reparatur-initiativen eintragen.</p>';
        return $html;
    }

    function getRoleHint($repairhelperInfotext, $orgaInfotext)
    {
        $html = '<strong>Wichtige Informationen über die verschiedenen Benutzerrollen</strong><br />
                <a class="open-with-featherlight" href="#repairhelperHelp" id="urlHelpLink">Was ist ein(e) <strong>Reparaturhelfer*in</strong> ?</a>
                <div class="hide">
                    <div id="repairhelperHelp" class="help-layer">
                    '.$repairhelperInfotext.'
                    </div>
                </div>
                &nbsp; &#x2016; &nbsp;
                <a class="open-with-featherlight" href="#orgaHelp" id="urlHelpLink">Was ist ein(e) <strong>Organisator*in</strong> ?</a>
                <div class="hide">
                    <div id="orgaHelp" class="help-layer">
                        '.$orgaInfotext.'
                    </div>
                </div>';
        return $html;
    }

    function urlEventDetail($workshopUrl, $eventUid, $eventDatumstart)
    {
        return $this->urlWorkshopDetail($workshopUrl) .'?event='.$eventUid.','.$eventDatumstart->i18nFormat(Configure::read('DateFormat.Database')).'#datum';
    }


    function urlUsers($categoryName = null, $zip=-1) {
        $url = '/aktive';
        if (!is_null($categoryName)) {
            $url .= '/' . StringComponent::slugify($categoryName);
        }
        if ($zip >= 0) {
            $url .= '?zip=' . $zip;
        }
        return $url;
    }

    function urlUserProfile($userUid) {
        return '/users/profile/'.$userUid;
    }

    function urlUserHome() {
        return '/users/welcome';
    }

    function urlFundings() {
        return '/mein-foerderantrag';
    }

    function urlFundingsEdit($workshopUid) {
        return $this->urlFundings() . '/edit/' . $workshopUid;
    }

    function urlFundingsAdminEdit($fundingUid) {
        return '/admin/fundings/edit/' . $fundingUid;
    }

    function urlFundinguploadDetail($fundinguploadId) {
        return $this->urlFundings() . '/uploadDetail/' . $fundinguploadId;
    }

    function getUserBackendNaviLinks($userUid, $isMyProfile, $isOrga) {
        $result = [];
        $result[] = ['url' => $this->urlUserHome(), 'name' => 'INFO'];
        $result[] = ['url' => $this->urlUserEdit($userUid, $isMyProfile), 'name' => 'MEIN PROFIL'];
        if ($isOrga) {
            $result[] = ['url' => $this->urlUserWorkshopAdmin(), 'name' => 'MEINE INITIATIVEN'];
        }
        $result[] = ['url' => $this->urlMyEvents(), 'name' => 'MEINE TERMINE'];
        $result[] = ['url' => '/initiativen/mitmachen', 'name' => 'MITMACHEN'];
        if (Configure::read('AppConfig.fundingsEnabled') && $isOrga) {
            $result[] = ['url' => $this->urlFundings(), 'name' => 'FÖRDERANTRAG'];
        }
        return $result;
    }

    function urlMyEvents()
    {
        return '/meine-termine';
    }

    /**
     * for google geocoder
     * eg: Mayrhofstr. 4 => Mayrhofstraße 4 / Test Str. => Test Straße
     * @param string $string
     * @return $string
     */
    function replaceAddressAbbreviations($string) {
        $string = preg_replace('/(s)tr\./i', '$1traße', $string);
        return $string;
    }

    function getFacebookUrl($username) {
        return 'https://www.facebook.com/' . $username . '/';
    }

    public function trimAndRemoveEmptyTags($html) {

        $pattern = "/<[^\/>]*>([\s]?)*<\/[^>]*>/";
        $html = preg_replace($pattern, '', $html);

        return trim($html);
    }

    public function __construct(View $View, array $config = [])
    {
        $this->helpers[] = 'Number';
        $this->_defaultConfig['templates']['javascriptblock'] = "{{content}}";
        parent::__construct($View, $config);
    }

    function wrapJavascriptBlock($content) {
        return "<script>
            //<![CDATA[
                $(document).ready(function() {
                    ".$content."
                });
            //]]>
        </script>";
    }

    public $selectedMain = [];
    public $selectedSub1 = [];
    public $selectedSub2 = [];
    public $selectedSub3 = [];

    public $selectParentElements = true;

    function urlPageDetail($url, $preview=false) {
        $previewSuffix = '';
        if ($preview == true) {
            $previewSuffix = '/vorschau';
        }
        return '/seite/' . $url . $previewSuffix;
    }

    /**
     * for admin edit
     */
    function isUrlEditable($object) {
        if ($object->status == APP_OFF) return true;
        return false;
    }

    function urlRegister() {
        return '/registrierung';
    }

    function urlRegisterRepairhelper() {
        return '/registrierung/reparaturhelferin';
    }
    function urlRegisterOrga() {
        return '/registrierung/organisatorin';
    }

    function urlLogin($redirect='') {
        $url = '/users/login';
        if ($redirect != '') {
            $url .= '?redirect='.$redirect;
        }
        return $url;
    }
    function urlLogout() {
        return '/users/logout';
    }
    function urlPasswortAendern() {
        return '/users/passwortAendern';
    }
    function urlUserWorkshopAdmin() {
        return '/initiativen/verwalten';
    }
    function urlUserWorkshopApplicationUser() {
        return '/initiativen/mitmachen';
    }
    function urlUserWorkshopApprove($type, $userUid, $workshopUid) {
        return '/initiativen/user/approve/' . $type . '/' . $userUid . '/' . $workshopUid;
    }
    function urlUserWorkshopResign($type, $userUid, $workshopUid) {
        return '/initiativen/user/resign/' . $type . '/' . $userUid . '/' . $workshopUid;
    }
    function urlUserWorkshopRefuse($type, $userUid, $workshopUid) {
        return '/initiativen/user/refuse/' . $type . '/' . $userUid . '/' . $workshopUid;
    }
    function urlNeuesPasswortAnfordern() {
        return '/users/neuesPasswortAnfordern';
    }

    function urlCategoryNew() {
        return '/admin/categories/insert/';
    }
    function urlCategoryEdit($id) {
        return '/admin/categories/edit/' . $id;
    }
    function urlOrdsCategoryNew() {
        return '/admin/ordsCategories/insert/';
    }
    function urlOrdsCategoryEdit($id) {
        return '/admin/ordsCategories/edit/' . $id;
    }
    function urlSkillNew() {
        return '/admin/skills/insert/';
    }
    function urlSkillEdit($id) {
        return '/admin/skills/edit/' . $id;
    }
    function urlBrandNew() {
        return '/admin/brands/insert/';
    }
    function urlBrandEdit($id) {
        return '/admin/brands/edit/' . $id;
    }
    function urlEventDelete($uid) {
        return '/termine/delete/' . $uid;
    }
    function urlEventEdit($uid) {
        return '/termine/edit/' . $uid;
    }
    function urlEventDuplicate($uid) {
        return '/termine/duplicate/' . $uid;
    }
    function urlEventNew($workshopUid = null) {
        return '/termine/add' . (!is_null($workshopUid) ? '/'.$workshopUid : '');
    }
    function urlInfoSheetNew($eventUid) {
        return '/laufzettel/add/' . $eventUid;
    }
    function urlInfoSheetEdit($infoSheetUid) {
        return '/laufzettel/edit/' . $infoSheetUid;
    }
    function urlInfoSheetDelete($infoSheetUid) {
        return '/laufzettel/delete/' . $infoSheetUid;
    }

    function urlFeed() {
        return '/feed.rss';
    }

    function getThumbs50Image($image, $objectType) {
        return FILES_DIR . 'uploadify/' . $objectType . '/thumbs-50/' . $image;
    }

    function getThumbs100Image($image, $objectType) {
        return FILES_DIR . 'uploadify/' . $objectType . '/thumbs-100/' . $image;
    }

    function getThumbs150Image($image, $objectType) {
        return FILES_DIR . 'uploadify/' . $objectType . '/thumbs-150/' . $image;
    }

    function getThumbs300Image($image, $objectType) {
        return FILES_DIR . 'uploadify/' . $objectType . '/thumbs-300/' . $image;
    }

    function getOriginalImage($image, $objectType) {
        return FILES_DIR . 'uploadify/' . $objectType . '/' . $image;
    }

    function getThumbs280ImageMultiple($image) {
        return FILES_DIR . 'multiple/thumbs-280/' . $image;
    }

    function getThumbs800ImageMultiple($image) {
        return FILES_DIR . 'multiple/thumbs-800/' . $image;
    }
    function urlSkills()
    {
        return '/kenntnisse';
    }
    function urlWorkshops($keyword = '')
    {
        $url = '/orte';
        if ($keyword != '') {
            $url .= '?keyword=' . $keyword;
        }
        return $url;
    }
    function urlEvents($keyword = '')
    {
        $url = '/termine';
        if ($keyword != '') {
            $url .= '?keyword=' . $keyword;
        }
        return $url;
    }
    function urlSkillDetail($id, $name, $zip=-1)
    {
        $url = '/aktive/' . $id . '-' . StringComponent::slugify($name);
        if ($zip >= 0) {
            $url .= '?zip=' . $zip;
        }
        return $url;
    }
    function urlBlogDetail($url) {
        return '/' . $url;
    }
    function urlWorkshopNew() {
        return '/initiativen/anlegen';
    }
    function urlWorkshopEdit($uid) {
        return '/initiativen/bearbeiten/'.$uid;
    }
    function urlWorkshopDelete($uid) {
        return '/initiativen/loeschen/'.$uid;
    }

    function urlWorkshopDetail($url) {
        return '/' . $url;
    }
    function urlPageEdit($uid) {
        return '/admin/pages/edit/'.$uid;
    }
    function urlPageNew() {
        return '/admin/pages/insert';
    }
    function urlPostEdit($uid) {
        return '/admin/posts/edit/'.$uid;
    }
    function urlPostNew($type='') {
        return '/admin/posts/insert/'.$type;
    }
    function urlKnowledgeNew() {
        return '/admin/knowledges/insert';
    }
    function urlKnowledgeEdit($uid) {
        return '/admin/knowledges/edit/'.$uid;
    }
    function urlKnowledgeDetail($uid) {
        return $this->urlKnowledges() . '/#' . $uid;
    }
    function urlKnowledges() {
        return '/reparaturwissen';
    }
    public function getPostTypesWithPreview() {
        return [
            'neuigkeiten',
            Configure::read('AppConfig.htmlHelper')->getAdditionalBlogCategoryUrl()
        ];
    }
    function urlPostDetail($url, $preview=false) {
        $previewSuffix = '';
        if ($preview == true) {
            $previewSuffix = '/vorschau';
        }
        return '/post/' . $url . $previewSuffix;
    }
    function urlUserEdit($uid, $isMyProfile) {
        $url = '/users/profil';
        if (!$isMyProfile) {
            $url .= '/'.$uid;
        }
        return $url;
    }

    public function getUserProfileImageSrc($user, $userImage)
    {
        $userImageSrc = '/files/uploadify/users/thumbs-150/' . $userImage;
        if(empty($userImage)) {
            if (!empty($user->categories)) {
                $categoryIdForUserProfileImage = $user->categories[rand(0, count($user->categories) - 1)]->id;
            } else {
                $path = WWW_ROOT . '/img/user-profile';
                $dir = new \DirectoryIterator($path);
                $files = [];
                foreach ($dir as $fileinfo) {
                    if ($fileinfo->isFile() && $fileinfo->getExtension() == 'png') {
                        $files[] = $fileinfo->getFilename();
                    }
                }
                $categoryIdForUserProfileImage = preg_replace('/[^0-9]/', '', $files[rand(0, count($files) - 1)]);
            }
            $userImageSrc = '/img/user-profile/user-profile-image-'.$categoryIdForUserProfileImage.'.png';
        }
        return $userImageSrc;
    }

    function getUserProfileImage($user)
    {
        $userAltText = isset($user->image_alt_text) ? $user->image_alt_text : $user['image_alt_text'];
        $userImage = isset($user->image) ? $user->image : $user['image'];
        $userImageSrc = $this->getUserProfileImageSrc($user, $userImage);
        $imageHtml = '<img alt="'.$userAltText.'"  class="rounded" src="'.$userImageSrc.'" >';
        return $imageHtml;
    }

    function urlUserNew() {
        return '/users/add';
    }

    function getUserGroupsForRegistration() {
        $userGroups = [
            GROUPS_ORGA         => 'Organisator*in',
            GROUPS_REPAIRHELPER => Configure::read('AppConfig.repairHelperName'),
        ];
        return $userGroups;
    }

    function getUserGroupsForWorkshopDetail() {
        $userGroups = [
            GROUPS_ADMIN        => 'Admin',
            GROUPS_ORGA         => 'Organisator*in',
            GROUPS_REPAIRHELPER => Configure::read('AppConfig.repairHelperName'),
        ];
        return $userGroups;
    }

    function getUserGroups() {
        $userGroups = [
            GROUPS_ADMIN       => 'Admin'
        ];
        return $userGroups;
    }

    function getUserGroupsForUserEdit($isAdmin = false) {
        $userGroups = [];
        if ($isAdmin) {
            $userGroups[GROUPS_ADMIN] = 'Admin';
        }
        $userGroups[GROUPS_ORGA] = 'Organisator*in';
        $userGroups[GROUPS_REPAIRHELPER] = Configure::read('AppConfig.repairHelperName');
        return $userGroups;
    }

    function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    function getPhotoDimensions($dimension) {
        if (!preg_match('/x/', $dimension)) return false;
        return explode('x', $dimension);
    }

    /**
     * creates navigation with up to 2 sublevels
     */
    function createMenuEntry($menuElement, $order = null, $mainMenuElement = null) {

        $element = '';

        if ($menuElement['level'] == 'main') {
            $element .= '';
        }

        $class = '';
        $element .= '<li ' . $class . '>';

        $htmlAttributes = [];
        if (isset($menuElement['htmlAttributes'])) {
            $htmlAttributes = array_merge($htmlAttributes, $menuElement['htmlAttributes']);
        }
        if ('/' . $this->here == $menuElement['url']) {

            if (isset($htmlAttributes['class'])) {
                // $htmlAttributes['class'] .= ' selected';
            } else {
                // $htmlAttributes['class'] = ' selected';
            }

            if ($menuElement['level'] == 'main') {
                $this->selectedMain = $menuElement;
            }

            if ($menuElement['level'] == 'sub1') {
                $this->selectedMain = $mainMenuElement;
                $this->selectedSub1 = $menuElement;
            }
        }

        if (isset($menuElement['sub'])) {

            foreach ($menuElement['sub'] as $sub1MenuElement) {

                if ('/' . $this->here == $sub1MenuElement['url']) {

                    if ($this->selectParentElements && in_array($menuElement['level'], ['main', 'sub'])) {
                        // $htmlAttributes['class'] = 'selected';
                    }

                    if ($menuElement['level'] == 'sub1') {
                        $this->selectedMain = $mainMenuElement;
                        $this->selectedSub1 = $menuElement;
                        $this->selectedSub2 = $sub1MenuElement;
                    }

                    continue;
                }

                if (isset($sub1MenuElement['sub'])) {
                    foreach ($sub1MenuElement['sub'] as $sub2MenuElement) {
                        if ('/' . $this->here == $sub2MenuElement['url']) {
                            // $htmlAttributes['class'] = 'selected';
                            continue;
                        }
                    }
                }
            }
        }

        $element .= self::link($menuElement['name'], $menuElement['url'], $htmlAttributes);

        if (isset($menuElement['sub'])) {
            $i = 0;
            $element .= '<ul class="submenu">';
            foreach ($menuElement['sub'] as $subMenuElement) {
                $element .= $this->createMenuEntry($subMenuElement, $i, $menuElement);
                $i++;
            }
            $element .= '</ul>';
        }

        $element .= '</li>';

        if ($menuElement['level'] == 'main') {
            $element .= '';
        }

        return $element;

    }

}