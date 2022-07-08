<?php

declare(strict_types=1);

namespace Tuleap\Onlyoffice\Controller;

use HTTPRequest;
use TemplateRendererFactory;
use Tuleap\Layout\BaseLayout;
use Tuleap\Request\DispatchableWithBurningParrot;
use Tuleap\Request\DispatchableWithRequest;
use Psr\Log\LoggerInterface;

use Tuleap\Onlyoffice\AppConfig;
use Tuleap\Onlyoffice\Presenters\EditorPresenter;

class EditorController implements DispatchableWithRequest, DispatchableWithBurningParrot
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AppConfig
     */
    private $appConfig;

    public function __construct (
        AppConfig $appConfig,
        LoggerInterface $logger
    ) {
        $this->appConfig = $appConfig;
        $this->logger = $logger;
    }

    public function process(HTTPRequest $request, BaseLayout $layout, array $variables): void
    {
        $fileId = (int)$request->getValidated('fileId', 'uint');

        $this->logger->debug('Open file in editor: ' . $fileId);

        $documentServerUrl = $this->appConfig->GetDocumentServerUrl();

        $renderer = TemplateRendererFactory::build()->getRenderer(__DIR__ . '/../../../template');
        $renderer->renderToPage(
            'editor',
            new EditorPresenter(
                $documentServerUrl,
                $fileId
            )
        );

        $layout->footer(['without_content' => true]);
    }
}