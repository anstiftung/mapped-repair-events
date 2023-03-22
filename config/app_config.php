<?php

define('LF', "\n");
define('TAB', "\t");

define('APP_ON', 1);
define('APP_OFF', 0);
define('APP_DELETED', -1);

define('WEBMASTER_TOOL_KEY', '');
define('JS_NAMESPACE', 'MappedRepairEvents');

define('PACKED_DIR', 'packed/');
define('COMPILED_DIR', 'compiled/');
define('TMP_DIR', 'tmp/');
define('CACHE_DIR', TMP_DIR . 'cache/');
define('CACHE_MODELS_DIR', CACHE_DIR . 'models/');
define('CACHE_PERSISTENT_DIR', CACHE_DIR . 'persistent/');
define('CACHE_VIEWS_DIR', CACHE_DIR . 'views/');

define('ALLOWED_TAGS_CKEDITOR_USER', '<a><b><strong><i><img><p><h1><h2><ul><li><hr><em><br>');
define('ALLOWED_TAGS_CKEDITOR_ADMIN', ALLOWED_TAGS_CKEDITOR_USER . '<iframe>');
define('ALLOWED_TAGS_USER', '<a><b><strong><i><br><p><ul><li>'); // für textfelder (zb. additional_contact)

define('FILES_DIR', '/files/');

define('GROUPS_ADMIN', 		      2);
define('GROUPS_REPAIRHELPER', 	  7);
define('GROUPS_ORGA', 	          9);

define('PASSWORD_REGEX', '/^([^\\s]){10,32}$/');
define('ZIP_REGEX', '/^[0-9]{4,5}$/');

return [
    'AppConfig' => [
        'platformName' => 'Meine Plattform',
        'titleSuffix' => 'Meine Plattform',
        'initiativeNameSingular' => 'Initiative',
        'initiativeNamePlural' => 'Initiativen',
        'categoriesNameWorkshops' => 'Reparatur-Bereiche',
        'categoriesNameUsers' => 'Reparatur-Kenntnisse',
        'claim' => 'Reparatur-Initiativen finden, unterstützen und gründen - Vernetzung, Beratung und Austausch',
        'specialEventNameSingular' => 'Reparaturtermin',
        'specialEventNamePlural' => 'Reparaturtermine',
        'registerAsOrgaInfoText' => 'Du planst oder organisierst bereits eine Initiative?<br /> Du bist AnsprechpartnerIn und koordinierst die Mitwirkenden?',
        'registerAsRepairhelperInfoText' => '<p>
                Du bist handwerklich geschickt und möchtest dein Wissen weitergeben?<br />
                Du bist bereits als als ReparateurIn in einer Initiative aktiv?<br />
                Du unterstützt ein Repair-Café beim Empfang, Cafébetrieb oder auf anderem Wege?<br />
                <br /> <strong>Registriere dich als ReparaturhelferIn und</strong>
            </p>
            <br />
            <ul>
                <li>gib Kenntnisse und Spezialgebiete an</li>
                <li>tritt Reparatur-Initiativen bei</li>
                <li>teile dein Wissen</li>
            </ul>',
        'thirdPartyStatisticsProviderName' => 'Statistik-Daten Drittanbieter',
        'mainCategoryIdsWhereSubCategoriesAreShown' => [504],
        'status' => [APP_ON => 'online', APP_OFF => 'offline', APP_DELETED => 'gelöscht'],
        'approvalStatus' => [APP_ON => 'bestätigt', APP_OFF => 'unbestätigt', APP_DELETED => 'gelöscht'],
        'status2' => [APP_ON => 'online', APP_OFF => 'offline'],
        'yesno' => [0=>'nein',1=>'ja'],
        'debugMailAddress' => 'office@example.com',
        'additionalBackupNotificationReceivers' => [],
        'notificationMailAddress' => 'office@example.com',
        'statisticsEnabled' => true,
        'mapWidgetEnabled' => true,
        'activeUsersFrontendEnabled' => true,
        'tagsFrontendEnabled' => true,
        'onlineEventsEnabled' => true,
        'showLastRowOnHome' => true,
        'adminUserUid' => 1,
        'additionalBlogCategoryEnabled' => false,
        'serverName' => 'http://www.example.org',
        'additionalBlogCategoryName' => 'Kategorie 2',
        'tmpWwwDir' => '/tmp',
        'tmpUploadFileSize' => 800,
        'twitterUsername' => '',
        'facebookUsername' => '',
        'externNewsletterUrl' => 'https://civicrm.anstiftung.de/civicrm_newsletter/subscribe/default',
        'splitterPath' => '/files/kcfinder/pages/15203',
        'tmpUploadImagesDir' => '/tmp/images',
        'thumbSizes' => [
             '50' => [],
            '100' => [],
            '150' => ['square' => true], // profil-bilder
            '300' => []
        ],
        'thumbSizesMultiple' => [
            '280' => [],
            '800' => [] //800 is maximum! (tmpUploadFileSize)
        ],
        'numberHelper' => new Cake\View\Helper\NumberHelper(new Cake\View\View()),
        'htmlHelper' => new App\View\Helper\MyHtmlHelper(new Cake\View\View()),
        'timeHelper' => new App\View\Helper\MyTimeHelper(new Cake\View\View()),
        'widgetHelper' => new App\View\Helper\WidgetHelper(new Cake\View\View())
    ],
    'DateFormat' => [
        'Database' => 'yyyy-MM-dd',
        'de' => [
            'DateShort' => 'dd.MM.yy',
            'DateLong' =>  'dd. MMMM y',
            'DateLong2' => 'dd.MM.yyyy',
            'DateNTimeShort' => 'dd.MM.y HH:mm',
            'TimeShort' => 'HH:mm',
            'TimeWithSeconds' => 'HH:mm:ss',
            'DateNTimeForDatepicker' => 'dd.MM.yyyy HH:mm'
        ]
    ],
    'Error' => [
        'ignoredDeprecationPaths' => [
            'vendor/cakephp/cakephp/src/I18n/Time.php',
            'vendor/dereuromark/cakephp-feed/src/View/RssView.php',
        ]
    ],
];

?>