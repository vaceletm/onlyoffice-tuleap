<?php

use Laminas\HttpHandlerRunner\Emitter\SapiStreamEmitter;
use Tuleap\Admin\SiteAdministrationAddOption;
use Tuleap\Admin\SiteAdministrationPluginOption;
use Tuleap\Layout\IncludeAssets;
use Tuleap\Request\CollectRoutesEvent;
use Tuleap\Http\HTTPFactoryBuilder;
use Tuleap\Admin\AdminPageRenderer;
use Tuleap\Config\ConfigClassProvider;
use Tuleap\Config\ConfigDao;
use Tuleap\Config\PluginWithConfigKeys;

use Tuleap\Onlyoffice\Plugin\PluginInfo;
use Tuleap\Onlyoffice\Controller\GetSettingsController;
use Tuleap\Onlyoffice\Controller\SaveSettingsController;
use Tuleap\Onlyoffice\Controller\EditorController;
use Tuleap\Onlyoffice\Controller\DownloadController;
use Tuleap\Onlyoffice\REST\ResourcesInjector;
use Tuleap\Onlyoffice\AppConfig;

require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/../../docman/vendor/autoload.php';

require_once __DIR__ . "/../3rdparty/jwt/src/BeforeValidException.php";
require_once __DIR__ . "/../3rdparty/jwt/src/ExpiredException.php";
require_once __DIR__ . "/../3rdparty/jwt/src/SignatureInvalidException.php";
require_once __DIR__ . "/../3rdparty/jwt/src/JWT.php";
require_once __DIR__ . "/../3rdparty/jwt/src/Key.php";

class onlyofficePlugin extends Plugin implements PluginWithConfigKeys
{
    private static ?AppConfig $appConfig = null;

    public const PLUGIN_ADMIN_URL = '/plugins/onlyoffice/admin';

    public function __construct($id)
    {
        parent::__construct($id);
        $this->setScope(self::SCOPE_SYSTEM);

        $this->addHook(SiteAdministrationAddOption::NAME);
        $this->addHook(Event::BURNING_PARROT_GET_JAVASCRIPT_FILES);
        $this->addHook(CollectRoutesEvent::NAME);
        $this->addHook(Event::REST_RESOURCES);
        $this->addHook(Event::CONTENT_SECURITY_POLICY_SCRIPT_WHITELIST, 'allowResourse');
        
        bindtextdomain('tuleap-onlyoffice', __DIR__ . '/../site-content');
    }

    public function getPluginInfo(): PluginInfo
    {
        if (!$this->pluginInfo) {
            $this->pluginInfo = new PluginInfo($this);
        }
        return $this->pluginInfo;
    }

    public function getServiceShortname(): string
    {
        return 'plugin_onlyoffice';
    }

    /** 
     * @see Event::CollectRoutesEvent 
     */
    public function collectRoutesEvent(CollectRoutesEvent $event) : void
    {
        $event->getRouteCollector()->addGroup('/plugins/onlyoffice', function (FastRoute\RouteCollector $r) {
            $r->get('/admin', $this->getRouteHandler('routeGetSettings'));
            $r->post('/admin', $this->getRouteHandler('routePostSettings'));
            $r->get('/editor', $this->getRouteHandler('routeGetEditor'));
            $r->get('/download', $this->getRouteHandler('routeGetDownload'));
        });
    }

    public function routeGetSettings(): GetSettingsController
    {
        return new GetSettingsController(
            new AdminPageRenderer(),
            new \CSRFSynchronizerToken(self::PLUGIN_ADMIN_URL),
            \BackendLogger::getDefaultLogger(),
            self::GetAppConfig()
        );
    }

    public function routePostSettings(): SaveSettingsController
    {
        return new SaveSettingsController(
            self::GetAppConfig(),
            \BackendLogger::getDefaultLogger()
        );
    }

    public function routeGetEditor(): EditorController
    {
        return new EditorController(
            self::GetAppConfig(),
            \BackendLogger::getDefaultLogger()
        );
    }

    public function routeGetDownload(): DownloadController
    {
        return new DownloadController(
            new SapiStreamEmitter(),
            \BackendLogger::getDefaultLogger(),
            HTTPFactoryBuilder::responseFactory(),
            new \Docman_ItemFactory(),
            \UserManager::instance(),
            self::GetAppConfig()
        );
    }

    /**
     * @see Event::SiteAdministrationAddOption 
     */
    public function siteAdministrationAddOption(SiteAdministrationAddOption $addOption): void
    {
        $addOption->addPluginOption(
            SiteAdministrationPluginOption::build(
                'ONLYOFFICE',
                self::PLUGIN_ADMIN_URL
            )
        );
    }

    /**
     * @see Event::BURNING_PARROT_GET_JAVASCRIPT_FILES 
     */
    public function burning_parrot_get_javascript_files($params): void
    {
            $coreAssets = new IncludeAssets(
                __DIR__ . '/../frontend-assets',
                '/assets/onlyoffice'
            );

            if (str_starts_with($_SERVER['REQUEST_URI'], '/plugins/onlyoffice/editor')) {
                $params['javascript_files'][] = $coreAssets->getFileURL('onlyoffice-editor.js');
            }
            if (str_starts_with($_SERVER['REQUEST_URI'], '/plugins/document')) {
                $params['javascript_files'][] = $coreAssets->getFileURL('onlyoffice-documents.js');
            }
    }

    /** 
     * @see Event::CONTENT_SECURITY_POLICY_SCRIPT_WHITELIST 
     */
    public function allowResourse(array $params)
    {
        if (str_starts_with($_SERVER['REQUEST_URI'], '/plugins/onlyoffice/editor')) {
            $params['whitelist_scripts'][] = self::GetAppConfig()->GetDocumentServerUrl();
        }
    }

    /** 
     * @see Event::REST_RESOURCES 
     */
    public function restResources(array $params)
    {
        $injector = new ResourcesInjector();
        $injector->populate($params['restler']);
    }

    public function getConfigKeys(ConfigClassProvider $event): void
    {
        $event->addConfigClass(AppConfig::class);
    }

    public static function GetAppConfig(): AppConfig
    {
        if (self::$appConfig === null) {
            self::$appConfig = new AppConfig(
                new ConfigDao()
            );
        }

        return self::$appConfig;
    }
}
