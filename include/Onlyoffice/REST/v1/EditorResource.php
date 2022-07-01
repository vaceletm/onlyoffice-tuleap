<?php

namespace Tuleap\Onlyoffice\REST\v1;

use Tuleap\ServerHostname;
use Tuleap\Onlyoffice\FileUtility;
use Tuleap\Onlyoffice\Crypt;
use Tuleap\Onlyoffice\AppConfig;

class EditorResource
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

    public function __construct()
    {
        $this->logger = \BackendLogger::getDefaultLogger();
        $this->appConfig = \onlyofficePlugin::GetAppConfig();
        $this->userManager = \UserManager::instance();
        $this->itemFactory = new \Docman_ItemFactory();
    }

    /**
     * Get config
     *
     * @url    GET config/{fileId}
     *
     * @access hybrid
     *
     * @param int $fileId - file identifier
     */
    public function config($fileId): array
    {
        $this->logger->debug('Building config for: ' . $fileId);

        $user = $this->userManager->getCurrentUser();
        $userId = $user->getId();
        $userName = $user->getName();

        $file = $this->itemFactory->getItemFromDb($fileId);
        if (empty($file)) {
            $this->logger->error('Config: file not found: ' . $fileId);
            return ['error' => 'File not found'];
        }
        $version = $file->getCurrentVersion();

        $formats = $this->appConfig->GetFormats();

        $fileExtension = pathinfo($file->getTitle(), PATHINFO_EXTENSION);

        $format = $formats[$fileExtension];
        if (empty($format)) {
            $this->logger->error('Format does not support: ' . $fileId);
            return ['error' => 'Format does not support'];
        }

        $key = FileUtility::GetKey($file);
        $params = [
            'document' => [
                'url' => $this->GetDownloadUrl($fileId, $userId),
                'fileType' => $fileExtension,
                'key' => $key,
                'title' => $file->getTitle()
            ],
            'documentType' => $format["type"],
            'type' => 'desktop'
        ];

        if ($userId !== 0) {
            $params['editorConfig'] = [
                'lang' => $user->getLanguageID(),
                'user' => [
                    'id' => $userId,
                    'name' => $userName
                ]
            ];
        }

        $permissionManager = \Docman_PermissionsManager::instance($file->getGroupId());

        $canEdit = isset($format['edit']) && $format['edit'];
        $editable = $permissionManager->userCanWrite($user, $fileId);

        $params['document']['permissions']['edit'] = $editable;
        if ($canEdit && $editable) {
            $params['editorConfig']['callbackUrl'] = $this->GetCallbackUrl($fileId, $userId);
        } else if ($permissionManager->userCanRead($user, $fileId)) {
            $params['editorConfig']['mode'] = 'view';
        } else {
            $this->logger->error('User ' . $userId . ' does not have permissions for ' . $fileId);
            return ['error' => 'Access denied'];
        }

        if (!empty($this->appConfig->GetJwtSecret())) {
            $token = \Firebase\JWT\JWT::encode($params, $this->appConfig->GetJwtSecret(), 'HS256');
            $params['token'] = $token;
        }

        $this->logger->debug('Configuration is buit for ' . $fileId . ' with key ' . $key);

        return $params;
    }

    /**
     * Url for download file directly
     *
     * @param int $fileId - file identifier
     * @param int $userId - user identifier
     */
    private function GetDownloadUrl($fileId, $userId): string
    {
        $params = [
            'action' => 'download',
            'fileId' => $fileId,
            'userId' => $userId
        ];

        $hash = Crypt::GetHash($params);

        return ServerHostname::HTTPSUrl() . "/plugins/onlyoffice/download?hash=" . $hash;
    }

    /**
     * Url for sending status by document server
     *
     * @param int $fileId - file identifier
     * @param int $userId - user identifier
     */
    private function GetCallbackUrl($fileId, $userId): string
    {
        $params = [
            'action' => 'track',
            'fileId' => $fileId,
            'userId' => $userId
        ];

        $hash = Crypt::GetHash($params);

        return ServerHostname::HTTPSUrl() . "/api/onlyoffice/callback/track/" . $hash;
    }
}
