<?php

declare(strict_types=1);

namespace Tuleap\Onlyoffice\Controller;

use CSRFSynchronizerToken;
use HTTPRequest;
use Tuleap\Admin\AdminPageRenderer;
use Tuleap\Layout\BaseLayout;
use Tuleap\Request\DispatchableWithBurningParrot;
use Tuleap\Request\DispatchableWithRequest;
use Psr\Log\LoggerInterface;

use onlyofficePlugin;
use Tuleap\Onlyoffice\AppConfig;
use Tuleap\Onlyoffice\Presenters\AdminPresenter;

class GetSettingsController implements DispatchableWithRequest, DispatchableWithBurningParrot
{
    /**
     * @var AdminPageRenderer
     */
    private $admin_page_renderer;

    /**
     * @var CSRFSynchronizerToken
     */
    private $token;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AppConfig
     */
    private $appConfig;

    public function __construct (
        AdminPageRenderer $admin_page_renderer,
        CSRFSynchronizerToken $token,
        LoggerInterface $logger,
        AppConfig $appConfig
    ) {
        $this->admin_page_renderer = $admin_page_renderer;
        $this->token = $token;
        $this->logger = $logger;
        $this->appConfig = $appConfig;
    }

    public function process(HTTPRequest $request, BaseLayout $layout, array $variables): void
    {
        $this->logger->debug('ONLYOFFICE Get setting process');

        $this->admin_page_renderer->renderANoFramedPresenter(
            'ONLYOFFICE',
            __DIR__ . '/../../../template',
            'admin',
            new AdminPresenter(
                $this->token,
                $this->appConfig->GetDocumentServerUrl(),
                $this->appConfig->GetVerifySelfSignedOff()
            )
        );
    }
}
