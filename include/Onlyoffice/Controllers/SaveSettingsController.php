<?php

declare(strict_types=1);

namespace Tuleap\Onlyoffice\Controller;

use HTTPRequest;
use Feedback;
use CSRFSynchronizerToken;
use Tuleap\Layout\BaseLayout;
use Tuleap\Request\DispatchableWithBurningParrot;
use Tuleap\Request\DispatchableWithRequest;
use Psr\Log\LoggerInterface;

use Tuleap\Onlyoffice\AppConfig;

class SaveSettingsController implements DispatchableWithRequest, DispatchableWithBurningParrot
{
    /**
     * @var AppConfig
     */
    private $appConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct (
        AppConfig $appConfig,
        LoggerInterface $logger
    ) {
        $this->appConfig = $appConfig;
        $this->logger = $logger;
    }

    public function process(HTTPRequest $request, BaseLayout $layout, array $variables): void
    {
        $this->logger->debug("ONLYOFFICE Save setting process");

        $scrf_token = new CSRFSynchronizerToken($request->getFromServer('REQUEST_URI'));
        $scrf_token->check();

        $documentServerUrl = (string)$request->getValidated('document-server-url', 'string');
        $verifySelfSignedOff = (bool)$request->getValidated('verify-self-signed-off', 'uint');
        $jwtSecret = (string)$request->getValidated('jwt-secret', 'string');

        $this->appConfig->SetDocumentServerUrl($documentServerUrl);
        $this->appConfig->SetVerifySelfSignedOff($verifySelfSignedOff);
        $this->appConfig->SetJwtSecret($jwtSecret);

        $layout->addFeedback(
            Feedback::INFO,
            dgettext('tuleap-onlyoffice', 'Settings have been saved successfully')
        );

        $layout->redirect($request->getFromServer('REQUEST_URI'));
    }
}