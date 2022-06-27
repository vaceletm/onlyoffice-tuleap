<?php

declare(strict_types=1);

namespace Tuleap\Onlyoffice\Controller;

use Docman_ItemFactory;
use UserManager;

use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Tuleap\Request\DispatchablePSR15Compatible;
use Tuleap\Request\DispatchableWithRequestNoAuthz;
use Tuleap\Http\HTTPFactoryBuilder;
use Tuleap\Http\Response\BinaryFileResponseBuilder;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

use Tuleap\Onlyoffice\Crypt;

class DownloadController extends DispatchablePSR15Compatible implements DispatchableWithRequestNoAuthz
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var Docman_ItemFactory
     */
    private $itemFactory;

    public function __construct (
        EmitterInterface $emitter,
        LoggerInterface $logger,
        ResponseFactoryInterface $responseFactory,
        Docman_ItemFactory $itemFactory,
        UserManager $userManager
    ) {
        parent::__construct($emitter);

        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
        $this->itemFactory = $itemFactory;
        $this->userManager = $userManager;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->debug('Hanlde download file content');

        $queryParams = $request->getQueryParams();
        $hash = $queryParams['hash'];

        list($hashData, $error) = Crypt::ReadHash($hash);
        if ($hashData === null) {
            $this->logger->error('Download handler: Error when decrypt hash: ' . $error);
            return $this->responseFactory->createResponse(400);
        }
        if ($hashData->action !== 'download') {
            $this->logger->error('Download handler: invalid action');
            return $this->responseFactory->createResponse(400);
        }

        $fileId = $hashData->fileId;

        $this->logger->debug('Download file: ' . $fileId);

        $file = $this->itemFactory->getItemFromDb($fileId);
        if (empty($file)) {
            $this->logger->error('Download handler: file not found: ' . $fileId);
            return $this->responseFactory->createResponse(404);
        }

        $userId = $hashData->userId;
        $user = $this->userManager->getUserById($userId);
        if ($user === null) {
            $this->logger->error('Download handler: user not found');
            return $this->responseFactory->createResponse(400);
        }

        $permissionManager = \Docman_PermissionsManager::instance($file->getGroupId());
        if (!$permissionManager->userCanRead($user, $fileId)) {
            $this->logger->error('Download handler: access denied: ' . $fileId);
            return $this->responseFactory->createResponse(403);
        }

        $version = $file->getCurrentVersion();
        $filePath = $version->getPath();

        $responseBinary = new BinaryFileResponseBuilder($this->responseFactory, HTTPFactoryBuilder::streamFactory());

        return $responseBinary->fromFilePath(
            $request,
            $filePath,
            $version->getFilename(),
            $version->getFiletype()
        );
    }
}