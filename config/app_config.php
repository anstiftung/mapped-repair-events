<?php

use Cake\View\Helper\MyNumberHelper;

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

define('ALLOWED_TAGS_EDITOR_USER', '<a><b><strong><i><img><p><h1><h2><h3><ul><ol><li><hr><em><br><span><div>');
define('ALLOWED_TAGS_EDITOR_ADMIN', ALLOWED_TAGS_EDITOR_USER . '<iframe>');
define('ALLOWED_TAGS_USER', '<a><b><strong><i><br><p><ul><ol><li>'); // für textfelder (zb. additional_contact)

define('FILES_DIR', '/files/');

define('GROUPS_ADMIN', 		      2);
define('GROUPS_REPAIRHELPER', 	  7);
define('GROUPS_ORGA', 	          9);

define('PASSWORD_REGEX', '/^([^\\s]){10,32}$/');
define('ZIP_REGEX', '/^[0-9]{4,5}$/');
define('ZIP_REGEX_DE', '/^[0-9]{5}$/');
define('BIC_REGEX', '/^[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}([A-Z0-9]{3})?$/');
define('UUID_REGEX', '[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[0-5][a-fA-F0-9]{3}-[089aAbB][a-fA-F0-9]{3}-[a-fA-F0-9]{12}'); // see Validate::uuid()

return [
    'Email' => [
        'default' => [
            'emailFormat' => 'html',
            'charset' => 'utf-8',
        ],
    ],
    'isApiEnabled' => false,
    'AppConfig' => [
        'platformName' => 'Meine Plattform',
        'titleSuffix' => 'Meine Plattform',
        'categoriesNameWorkshops' => 'Reparatur-Bereiche',
        'categoriesNameUsers' => 'Reparatur-Kenntnisse',
        'metaTags' => [
            'Workshops' => [
                'all' => [
                    'title' => 'Initiativen finden, unterstützen und gründen',
                    'description' => 'Mach mit im Reparatur-Café!',
                    'keywords' => 'repair café, repair cafe, reparieren, repair, reparatur, reparatur-initiativen, netzwerk reparatur-initiativen'
                ],
                'home' => [
                    'title' => 'Initiativen finden, unterstützen und gründen',
                    'description' => 'Mach mit im Reparatur-Café! ',
                    'keywords' => 'repair café, repair cafe, reparieren, repair, reparatur, reparatur-initiativen, netzwerk reparatur-initiativen'
                ],
                'detail' => [
                    'descriptions' => [
                        '%name% hilft: Elektrogeräte, Unterhaltungselektronik, Kleidung uvm. gemeinsam reparieren in %city%. Mach mit!',
                        '%name%: Gemeinsam reparieren in %city%.',
                        '%name% - mach mit! Gemeinsam Elektrogeräte, Unterhaltungselektronik, Kleidung uvm. reparieren in %city%.',
                    ],
                    'keywords' => 'repair café, repair-café, repair, reparatur, repaircafé, reparieren, reparatur café, reparatur-initiativen',
                ],
            ],
            'Events' => [
                'all' => [
                    'title' => 'Suche Termine in deiner Nähe',
                    'description' => 'Termine und Veranstaltungen von Repair Cafés und anderen Initiativen in deiner Nähe',
                    'keywords' => 'repair café, repair cafe, reparieren, repair, reparatur, reparatur-initiativen, netzwerk reparatur-initiativen, reparaturtermin, reparaturveranstaltung'
                ],
            ],
            'Posts' => [
                'detail' => [
                    'keywords' => 'netzwerk reparatur-initiativen, reparatur-initiativen, anstiftung, repair café, repair-café, repair, reparatur, repaircafé, reparieren, reparatur café',
                ],
            ],
        ],
        'claim' => 'Reparatur-Initiativen finden und gründen - Vernetzung, Beratung, Austausch',
        'specialEventNameSingular' => 'Reparaturtermin',
        'specialEventNamePlural' => 'Reparaturtermine',
        'registerAsOrgaInfoText' => 'Du planst oder organisierst bereits eine Initiative?<br /> Du bist Ansprechpartner*in und koordinierst die Mitwirkenden?',
        'registerAsRepairHelperInfoText' => '<p>
                Du bist handwerklich geschickt und möchtest dein Wissen weitergeben?<br />
                Du bist bereits als als Reparateur*in in einer Initiative aktiv?<br />
                Du unterstützt ein Repair-Café beim Empfang, Cafébetrieb oder auf anderem Wege?<br />
                <br /> <strong>Registriere dich als Reparaturhelfer*in und</strong>
            </p>
            <br />
            <ul>
                <li>gib Kenntnisse und Spezialgebiete an</li>
                <li>tritt Reparatur-Initiativen bei</li>
                <li>teile dein Wissen</li>
            </ul>',
        'repairHelperName' => 'Reparaturhelfer*in',
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
        'fundingsEnabled' => true,
        'fundingsStartDate' => '2024-12-01',
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
        'themeColorA' => '#4D8EA2',
        'themeColorB' => '#e3d3e9',
        'themeColorC' => '#ffffff',
        'themeColorD' => '#EFAD11',
        'themeColorE' => '#F4C961',
        'themeColorF' => '#77B1C5',
        'themeColorG' => '#B57CDB',
        'themeColorH' => '#d7cbdfcc',
        'themeColorI' => '#d7cbdf',
        'themeFont' => 'Open Sans',
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
        'textHelper' => new Cake\View\Helper\TextHelper(new Cake\View\View()),
        'numberHelper' => new App\View\Helper\MyNumberHelper(new Cake\View\View()),
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
            'DateLong2WithWeekday' => 'EEEE, dd.MM.yyyy',
            'DateNTimeShort' => 'dd.MM.y HH:mm',
            'DateNTimeShortWithSeconds' => 'dd.MM.y HH:mm:ss',
            'DateNTimeLongWithSeconds' => 'dd.MM.yy HH:mm:ss',
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