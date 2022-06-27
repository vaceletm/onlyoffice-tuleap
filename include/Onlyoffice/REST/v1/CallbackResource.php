<?php

namespace Tuleap\Onlyoffice\REST\v1;

use Tuleap\Http\HTTPFactoryBuilder;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Luracast\Restler\RestException;

use Tuleap\Onlyoffice\Crypt;
use Tuleap\Onlyoffice\DocumentService;

class CallbackResource
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AppConfig
     */
    private $appConfig;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var Docman_ItemFactory
     */
    private $itemFactory;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * Status of the document
     */
    private const TrackerStatus_Editing = 1;
    private const TrackerStatus_MustSave = 2;
    private const TrackerStatus_Corrupted = 3;
    private const TrackerStatus_Closed = 4;

    public function __construct()
    {
        $this->logger = \BackendLogger::getDefaultLogger();
        $this->appConfig = \onlyofficePlugin::GetAppConfig();
        $this->userManager = \UserManager::instance();
        $this->itemFactory = new \Docman_ItemFactory();
        $this->responseFactory = HTTPFactoryBuilder::responseFactory();
    }

    /**
     * Handle request from the document server with the document status information
     *
     * @url    POST track/{hash}
     *
     * @access public
     *
     * @param string $fileId Id of the file
     * @param integer $status {@from body} {@type integer}
     * @param string $key {@from body} {@type string}
     * @param array $users {@from body} {@type string}
     * @param string $url {@from body} {@type string}
     *
     * @throws RestException
     */
    public function track($hash, $status, $key, $users = null, $url = null): array
    {
        list($hashData, $error) = Crypt::ReadHash($hash);
        if ($hashData === null) {
            $this->logger->error('Track: Error when decrypt hash: ' . $error);
            return $this->responseFactory->createResponse(400);
        }
        if ($hashData->action !== 'track') {
            $this->logger->error('Track: Invalid action');
            return $this->responseFactory->createResponse(400);
        }

        $fileId = $hashData->fileId;

        $this->logger->debug('Track for file: ' . $fileId);

        $result = 1;
        switch ($status) {
            case self::TrackerStatus_MustSave:
            case self::TrackerStatus_Corrupted:
                if (empty($url)) {
                    $logger->error('Track without url');
                    throw new RestException(400, 'Track without url');
                }

                $userId = null;
                if (!empty($users)) {
                    $userId = $users[0];
                }

                $user = $this->userManager->getUserById($userId);
                if ($user === null) {
                    $this->logger->error('Track: user ' . $userId . 'not found');
                    throw new RestException(400, 'Invalid user');
                }

                $file = $this->itemFactory->getItemFromDb($fileId);
                if (empty($file)) {
                    $this->logger->error('Track: file not found: ' . $fileId);
                    throw new RestException(404, 'File not found');
                }

                try {
                    $documentService = new DocumentService($this->appConfig);
    
                    $response = $documentService->Request($url);
                    $content = $response->getBody()->getContents();

                    $this->SaveFile($file, $user, $content);
    
                    $result = 0;
                } catch (\Exception $exception) {
                    $this->logger->error('Track: file ' . $fileId . ' status ' . $status . ' error: ' . $exception->getMessage());
                }
                break;
            
            case self::TrackerStatus_Editing:
            case self::TrackerStatus_Closed:
                $result = 0;
                break;
        }

        return ["error" => $result];
    }

    private function SaveFile($item, $user, $newContent): void
    {
        $permissionManager = \Docman_PermissionsManager::instance($item->getGroupId());
        if (!$permissionManager->userCanWrite($user, $item->getId())) {
            throw new \Exception('User does not have enough permissions to write file');
        }

        $docmanPlugin = \PluginManager::instance()->getPluginByName('docman');
        $docmanRoot   = $docmanPlugin->getPluginInfo()->getPropertyValueForName('docman_root');

        $docmanFileStorage = new \Docman_FileStorage($docmanRoot);
        $versionFactory = new \Docman_VersionFactory();

        $nextVersionId = (int)$versionFactory->getNextVersionNumber($item);

        $newFilePath = $docmanFileStorage->store($newContent, $item->getGroupId(), $item->getId(), $nextVersionId);
        if ($newFilePath === false) {
            throw new \Exception('Error occured when trying to store file to docman storage');
        }

        $wasVersionCreate = $versionFactory->create(
            [
                'item_id'   => $item->getId(),
                'number'    => $nextVersionId,
                'user_id'   => $user->getId(),
                'filename'  => $item->getTitle(),
                'filesize'  => strlen($newContent),
                'filetype'  => $item->getType(),
                'path'      => $newFilePath,
                'date'      => (new \DateTimeImmutable())->getTimestamp()
            ]
        );

        if (!$wasVersionCreate) {
            \unlink($newFilePath);
            throw new \Exception('Error occured when trying to create new file version');
        }

        $wasItemUpdate = $this->itemFactory->update(['id' => $item->getId()]);
        if (!$wasItemUpdate) {
            \unlink($newFilePath);
            $versionFactory->deleteSpecificVersion($item, $nextVersionId);
            throw new \Exception('Error occured when trying to update item');
        }
    }
}