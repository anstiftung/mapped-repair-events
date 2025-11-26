<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\View;
use Cake\Core\Configure;
use Cake\View\Helper\HtmlHelper;
use App\Controller\Component\StringComponent;
use App\Model\Entity\Workshop;
use App\Model\Entity\Funding;
use Cake\Datasource\EntityInterface;
use Cake\View\Helper\FormHelper;
use App\Model\Entity\User;
use Cake\I18n\Date;

class MyHtmlHelper extends HtmlHelper {

    /**
     * @var array<string, string>
     */
    public array $selectedMain = [];

    /**
     * @var array<string, string>
     */
    public array $selectedSub1 = [];

    /**
     * @var array<string, string>
     */
    public array $selectedSub2 = [];

    /**
     * @var array<string, string>
     */
    public array $selectedSub3 = [];

    public bool $selectParentElements = true;


    public function replaceFundingCheckboxPlaceholders(string $html, Funding $funding): string
    {
        $html = str_replace('{RESTBETRAG}', (string) Configure::read('AppConfig.numberHelper')->formatAsDecimal($funding->receiptlist_difference), $html);
        $html = str_replace('{UID}', (string) $funding->uid, $html);
        $html = str_replace('{KONTONUMMER}', 'anstiftung, DE75430609671082980800, GENODEM1GLS', $html);
        return $html;
    }

    public function getHostWithoutProtocol(string $hostnameWithProtocol): false|string
    {
        $parsedHostnameWithProtocol = (parse_url($hostnameWithProtocol));
        if (!empty($parsedHostnameWithProtocol['host'])) {
            return $parsedHostnameWithProtocol['host'];
        }
        return false;
    }

    /**
     * @return array<int, string>
     */
    public function getWorkshopStatisticTypes(): array
    {
        return [
            Workshop::STATISTICS_DISABLED => 'Keine Statistik im Profil anzeigen',
            Workshop::STATISTICS_SHOW_ALL => 'Gesamte Statistik anzeigen',
            Workshop::STATISTICS_SHOW_ONLY_CHART => 'Nur Zahlenstatistik im Profil anzeigen',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function getMenuTypes(): array
    {
        return [
            'no-menu' => 'In keinem Menü verlinken',
            'header' => 'Header (oben)',
            'footer' => 'Footer (unten)'
        ];
    }

    public function getAdditionalBlogCategoryUrl(): string
    {
        return StringComponent::slugify(Configure::read('AppConfig.additionalBlogCategoryName'));
    }

    public function getHostName(): string
    {
        $serverName = Configure::read('AppConfig.serverName');
        $parsedServerName = parse_url((string) $serverName)['host'];
        $parsedServerName = str_replace('www.', '', $parsedServerName);
        return $parsedServerName;
    }

    function getCarbonFootprintAsString(float $carbonFootprintSum): string
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

    public function getMaterialFootprintAsString(float $materialFootprintSum): string
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

    public function getWorkshopsCountForGlobalStatisticsString(int $workshopCount): string
    {
        return Configure::read('AppConfig.numberHelper')->precision($workshopCount, 0) . ' ' . 'Initiativen haben mit ihren Reparaturdaten zu dieser Statistik beigetragen.';
    }

    function roundUpToAny(float $n, float $x): float
    {
        return round($n * $x) / $x;
    }

    /**
     * @return array<string, string>
     */
    function getGenders(): array
    {
        return [
            'f' => 'weiblich',
            'm' => 'männlich'
        ];
    }

    function generateGenericRadioButton(FormHelper $form, EntityInterface $formField): string
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

    function addClassToFormInputContainer(string $class): string
    {
        return '<div class="'.$class.' input {{type}}">{{content}}</div>';
    }

    function addClassToFormInputContainerError(string $class): string
    {
        return '<div class="'.$class.' input {{type}}{{required}} error">{{content}}{{error}}</div>';
    }

    function getFacebookHint(): string
    {
        $html = '<strong>Facebook</strong>
         <p>Hier können nur Facebook-Seiten eingetragen werden, keine Personenprofile.
         Dazu ruft ihr eure Facebook-Seite im Browser auf und tragt alles, was <b>nach</b>  http://www.facebook.com/ steht in das Feld ein.
        Beispiel: NICHT https://www.facebook.com/netzwerk-reparatur-initiatven, sondern lediglich netzwerk-reparatur-initiativen eintragen.</p>';
        return $html;
    }

    function getRoleHint(string $repairhelperInfotext, string $orgaInfotext): string
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

    function urlEventDetail(string $workshopUrl, int $eventUid, Date $eventDatumstart): string
    {
        return $this->urlWorkshopDetail($workshopUrl) .'?event='.$eventUid.','.$eventDatumstart->i18nFormat(Configure::read('DateFormat.Database')).'#datum';
    }


    function urlUsers(?string $categoryName = null, string|int $zip=-1): string
    {
        $url = '/aktive';
        if (!is_null($categoryName)) {
            $url .= '/' . StringComponent::slugify($categoryName);
        }
        if ($zip >= 0) {
            $url .= '?zip=' . $zip;
        }
        return $url;
    }

    function urlUserProfile(int $userUid): string
    {
        return '/users/profile/'.$userUid;
    }

    function urlUserHome(): string
    {
        return '/users/welcome';
    }

    function urlFundings(): string
    {
        return '/mein-foerderantrag';
    }

    function urlFundingsEdit(int $workshopUid): string
    {
        return $this->urlFundings() . '/edit/' . $workshopUid;
    }

    function urlFundingsUsageproof(int $fundingUid): string
    {
        return $this->urlFundings() . '/verwendungsnachweis/' . $fundingUid;
    }

    function urlFundingsConfirmEvents(int $fundingUid): string
    {
        return $this->urlFundings() . '/confirmEvents/' . $fundingUid;
    }

    function urlFundingsUploadZuwendungsbestaetigung(int $fundingUid): string
    {
        return $this->urlFundings() . '/uploadZuwendungsbestaetigung/' . $fundingUid;
    }

    function urlFundingsAdminEdit(int $fundingUid): string
    {
        return '/admin/fundings/edit/' . $fundingUid;
    }

    function urlFundinguploadDetail(string $fundinguploadId): string
    {
        return $this->urlFundings() . '/uploadDetail/' . $fundinguploadId;
    }

    function urlFundingFoerderantragDownload(int $fundingUid): string
    {
        return $this->urlFundings() . '/download/foerderantrag/' . $fundingUid;
    }

    function urlFundingFoerderbewilligungDownload(int $fundingUid): string
    {
        return $this->urlFundings() . '/download/foerderbewilligung/' . $fundingUid;
    }

    function urlFundingVerwendungsnachweisDownload(int $fundingUid): string
    {
        return $this->urlFundings() . '/download/verwendungsnachweis/' . $fundingUid;
    }

    function urlFundingsDelete(int $fundingId): string
    {
        return $this->urlFundings() . '/delete/' . $fundingId;
    }

    /**
     * @return array<array{"url": string, "name": string}>
     */
    function getUserBackendNaviLinks(int $userUid, bool $isMyProfile, bool $isOrga): array
    {
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

    function urlMyEvents(): string
    {
        return '/meine-termine';
    }

    /**
     * for google geocoder
     * eg: Mayrhofstr. 4 => Mayrhofstraße 4 / Test Str. => Test Straße
     */
    function replaceAddressAbbreviations(string $string): string
    {
        return preg_replace('/(s)tr\./i', '$1traße', $string);
    }

    function getFacebookUrl(string $username): string
    {
        return 'https://www.facebook.com/' . $username . '/';
    }

    public function trimAndRemoveEmptyTags(string $html): string
    {
        $pattern = "/<[^\/>]*>([\s]?)*<\/[^>]*>/";
        $html = preg_replace($pattern, '', $html);
        return trim((string) $html);
    }

    public function __construct(View $View, array $config = [])
    {
        $this->helpers[] = 'Number';
        $this->_defaultConfig['templates']['javascriptblock'] = "{{content}}";
        parent::__construct($View, $config);
    }

    function wrapJavascriptBlock(string $content): string
    {
        return "<script>
            //<![CDATA[
                $(document).ready(function() {
                    ".$content."
                });
            //]]>
        </script>";
    }

    function urlPageDetail(string $url, bool $preview=false): string
    {
        $previewSuffix = '';
        if ($preview == true) {
            $previewSuffix = '/vorschau';
        }
        return '/seite/' . $url . $previewSuffix;
    }

    /**
     * for admin edit
     */
    function isUrlEditable(EntityInterface $object): bool
    {
        return $object->status == APP_OFF;
    }

    function urlRegister(): string
    {
        return '/registrierung';
    }

    function urlRegisterRepairhelper(): string
    {
        return '/registrierung/reparaturhelferin';
    }

    function urlRegisterOrga(): string
    {
        return '/registrierung/organisatorin';
    }

    function urlLogin(?string $redirect=''): string
    {
        $url = '/users/login';
        if ($redirect != '') {
            $url .= '?redirect='.$redirect;
        }
        return $url;
    }
    function urlLogout(): string
    {
        return '/users/logout';
    }

    function urlPasswortAendern(): string
    {
        return '/users/passwortAendern';
    }

    function urlUserWorkshopAdmin(): string
    {
        return '/initiativen/verwalten';
    }

    function urlUserWorkshopApplicationUser(): string
    {
        return '/initiativen/mitmachen';
    }

    function urlUserWorkshopApprove(string $type, int $userUid, int $workshopUid): string
    {
        return '/initiativen/user/approve/' . $type . '/' . $userUid . '/' . $workshopUid;
    }

    function urlUserWorkshopResign(string $type, int $userUid, int $workshopUid): string
    {
        return '/initiativen/user/resign/' . $type . '/' . $userUid . '/' . $workshopUid;
    }

    function urlUserWorkshopRefuse(string $type, int $userUid, int $workshopUid): string
    {
        return '/initiativen/user/refuse/' . $type . '/' . $userUid . '/' . $workshopUid;
    }

    function urlNeuesPasswortAnfordern(): string
    {
        return '/users/neuesPasswortAnfordern';
    }

    function urlCategoryNew(): string
    {
        return '/admin/categories/insert/';
    }

    function urlCategoryEdit(int $id): string
    {
        return '/admin/categories/edit/' . $id;
    }
    function urlOrdsCategoryNew(): string
    {
        return '/admin/ordsCategories/insert/';
    }
    function urlOrdsCategoryEdit(int $id): string
    {
        return '/admin/ordsCategories/edit/' . $id;
    }
    function urlSkillNew(): string
    {
        return '/admin/skills/insert/';
    }
    function urlSkillEdit(int $id): string
    {
        return '/admin/skills/edit/' . $id;
    }
    function urlBrandNew(): string
    {
        return '/admin/brands/insert/';
    }
    function urlBrandEdit(int $id): string
    {
        return '/admin/brands/edit/' . $id;
    }
    function urlEventIcal(int $uid): string
    {
        return '/events/ical/' . $uid . '.ics';
    }
    function urlEventIcalAll(): string
    {
        return '/events.ics';
    }
    function urlEventDelete(int $uid): string
    {
        return '/termine/delete/' . $uid;
    }
    function urlEventEdit(int $uid): string
    {
        return '/termine/edit/' . $uid;
    }
    function urlEventDuplicate(int $uid, string $refererParams = ''): string
    {
        $url = '/termine/duplicate/' . $uid;
        $url = $this->addRefererParamsToUrl($url, $refererParams);
        return $url;
    }
    function urlEventNew(?int $workshopUid = null, string $refererParams = ''): string
    {
        $url = '/termine/add' . (!is_null($workshopUid) ? '/'.$workshopUid : '');
        $url = $this->addRefererParamsToUrl($url, $refererParams);
        return $url;
    }
    function urlInfoSheetNew(int $eventUid, string $refererParams = ''): string
    {
        $url = '/laufzettel/add/' . $eventUid;
        $url = $this->addRefererParamsToUrl($url, $refererParams);
        return $url;
    }
    function urlInfoSheetEdit(int $infoSheetUid, string $refererParams = ''): string
    {
        $url = '/laufzettel/edit/' . $infoSheetUid;
        $url = $this->addRefererParamsToUrl($url, $refererParams);
        return $url;
    }

    function urlInfoSheetDelete(int $infoSheetUid): string
    {
        return '/laufzettel/delete/' . $infoSheetUid;
    }

    function addRefererParamsToUrl(string $url, string $refererParams): string
    {
        return $refererParams != '' ? $url . '?refererParams=' . urlencode($refererParams) : $url;
    }

    function getKeyFromRefererParams(string $key, string $refererParams): int {
        $value = 0;
        if ($refererParams == '') {
            return $value;
        }
        $explodedRefererParams = explode(';', urldecode($refererParams));
        foreach ($explodedRefererParams as $param) {
            $paramParts = explode('=', $param);
            if (count($paramParts) == 2 && $paramParts[0] == $key) {
                $value = (int) $paramParts[1];
            }
        }
        return $value;
    }

    function urlFeed(): string
    {
        return '/feed.rss';
    }

    function getThumbs50Image(string $image, string $objectType): string
    {
        return FILES_DIR . 'uploadify/' . $objectType . '/thumbs-50/' . $image;
    }

    function getThumbs100Image(string $image, string $objectType): string
    {
        return FILES_DIR . 'uploadify/' . $objectType . '/thumbs-100/' . $image;
    }

    function getThumbs150Image(string $image, string $objectType): string
    {
        return FILES_DIR . 'uploadify/' . $objectType . '/thumbs-150/' . $image;
    }

    function getThumbs300Image(string $image, string $objectType): string
    {
        return FILES_DIR . 'uploadify/' . $objectType . '/thumbs-300/' . $image;
    }

    function getOriginalImage(string $image, string $objectType): string
    {
        return FILES_DIR . 'uploadify/' . $objectType . '/' . $image;
    }

    function getThumbs280ImageMultiple(string $image): string
    {
        return FILES_DIR . 'multiple/thumbs-280/' . $image;
    }

    function getThumbs800ImageMultiple(string $image): string
    {
        return FILES_DIR . 'multiple/thumbs-800/' . $image;
    }
    function urlSkills(): string
    {
        return '/kenntnisse';
    }
    function urlWorkshops(?string $keyword = ''): string
    {
        $url = '/orte';
        if ($keyword != '') {
            $url .= '?keyword=' . $keyword;
        }
        return $url;
    }
    function urlEvents(?string $keyword = ''): string
    {
        $url = '/termine';
        if ($keyword != '') {
            $url .= '?keyword=' . $keyword;
        }
        return $url;
    }
    function urlSkillDetail(int $id, string $name, string|int $zip=-1): string
    {
        $url = '/aktive/' . $id . '-' . StringComponent::slugify($name);
        if ($zip >= 0) {
            $url .= '?zip=' . $zip;
        }
        return $url;
    }
    function urlBlogDetail(string $url): string
    {
        return '/' . $url;
    }
    function urlWorkshopNew(): string
    {
        return '/initiativen/anlegen';
    }
    function urlWorkshopEdit(int $uid): string
    {
        return '/initiativen/bearbeiten/'.$uid;
    }
    function urlWorkshopDelete(int $uid): string
    {
        return '/initiativen/loeschen/'.$uid;
    }

    function urlWorkshopDetail(?string $url): string
    {
        return '/' . $url;
    }
    function urlPageEdit(int $uid): string
    {
        return '/admin/pages/edit/'.$uid;
    }
    function urlPageNew(): string
    {
        return '/admin/pages/insert';
    }
    function urlPostEdit(int $uid): string
    {
        return '/admin/posts/edit/'.$uid;
    }
    function urlPostNew(?string $type=''): string
    {
        return '/admin/posts/insert/'.$type;
    }
    function urlKnowledgeNew(): string
    {
        return '/admin/knowledges/insert';
    }
    function urlKnowledgeEdit(int $uid): string
    {
        return '/admin/knowledges/edit/'.$uid;
    }
    function urlKnowledgeDetail(int $uid): string
    {
        return $this->urlKnowledges() . '/#' . $uid;
    }
    function urlKnowledges(): string
    {
        return '/reparaturwissen';
    }

    /**
     * @return array<string>
     */
    public function getPostTypesWithPreview(): array
    {
        return [
            'neuigkeiten',
            Configure::read('AppConfig.htmlHelper')->getAdditionalBlogCategoryUrl()
        ];
    }
    function urlPostDetail(string $url, bool $preview=false): string
    {
        $previewSuffix = '';
        if ($preview == true) {
            $previewSuffix = '/vorschau';
        }
        return '/post/' . $url . $previewSuffix;
    }
    function urlUserEdit(int $uid, bool|string $isMyProfile): string
    {
        $url = '/users/profil';
        if (!$isMyProfile) {
            $url .= '/'.$uid;
        }
        return $url;
    }

    public function getUserProfileImageSrc(User $user, ?string $userImage): string
    {
        $userImageSrc = '/files/uploadify/users/thumbs-150/' . $userImage;
        if(empty($userImage)) {
            if (!empty($user->categories)) {
                $categoryIdForUserProfileImage = $user->categories[random_int(0, count($user->categories) - 1)]->id;
            } else {
                $path = WWW_ROOT . '/img/user-profile';
                $dir = new \DirectoryIterator($path);
                $files = [];
                foreach ($dir as $fileinfo) {
                    if ($fileinfo->isFile() && $fileinfo->getExtension() == 'png') {
                        $files[] = $fileinfo->getFilename();
                    }
                }
                $categoryIdForUserProfileImage = preg_replace('/[^0-9]/', '', $files[random_int(0, count($files) - 1)]);
            }
            $userImageSrc = '/img/user-profile/user-profile-image-'.$categoryIdForUserProfileImage.'.png';
        }
        return $userImageSrc;
    }

    function getUserProfileImage(User $user): string
    {
        $userAltText = $user->image_alt_text ?? $user->image_alt_text;
        $userImage = $user->image ?? $user->image;
        $userImageSrc = $this->getUserProfileImageSrc($user, $userImage);
        $imageHtml = '<img alt="'.$userAltText.'"  class="rounded" src="'.$userImageSrc.'" >';
        return $imageHtml;
    }

    function urlUserNew(): string
    {
        return '/users/add';
    }

    /**
     * @return array<int, string>
     */
    function getUserGroupsForRegistration(): array
    {
        return [
            GROUPS_ORGA         => 'Organisator*in',
            GROUPS_REPAIRHELPER => Configure::read('AppConfig.repairHelperName'),
        ];
    }

    /**
     * @return array<int, string>
     */
    function getUserGroupsForWorkshopDetail(): array
    {
        return [
            GROUPS_ADMIN        => 'Admin',
            GROUPS_ORGA         => 'Organisator*in',
            GROUPS_REPAIRHELPER => Configure::read('AppConfig.repairHelperName'),
        ];
    }

    /**
     * @return array<int, string>
     */
    function getUserGroups(): array
    {
        return [
            GROUPS_ADMIN       => 'Admin'
        ];
    }

    /**
     * @return array<int, string>
     */
    function getUserGroupsForUserEdit(bool $isAdmin = false): array
    {
        $userGroups = [];
        if ($isAdmin) {
            $userGroups[GROUPS_ADMIN] = 'Admin';
        }
        $userGroups[GROUPS_ORGA] = 'Organisator*in';
        $userGroups[GROUPS_REPAIRHELPER] = Configure::read('AppConfig.repairHelperName');
        return $userGroups;
    }

    function formatBytes(int $bytes, int $precision = 2) :string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= 1024 ** $pow;

        return number_format($bytes, $precision, ',', '.') . ' ' . $units[$pow];
    }

    /**
     * @return string[]
     */
    function getPhotoDimensions(string $dimension): array|false
    {
        if (!preg_match('/x/', $dimension)) return false;
        return explode('x', $dimension);
    }

    /**
     * @param array<string, string|array<mixed>> $menuElement
     * @param array<string, string|array<mixed>> $mainMenuElement
     */
    function createMenuEntry(array $menuElement, ?array $mainMenuElement = null): string
    {

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
                $element .= $this->createMenuEntry($subMenuElement, $menuElement);
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