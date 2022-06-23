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
        $this->logger->debug("Building config for: " . $fileId);

        $user = $this->userManager->getCurrentUser();
        $userId = $user->getId();
        $userName = $user->getName();

        $file = $this->itemFactory->getItemFromDb($fileId);
        if (empty($file)) {
            $this->logger->error('Config: file not found: ' . $fileId);
            return ["error" => "File not found"];
        }
        $version = $file->getCurrentVersion();

        $formats = $this->appConfig->GetFormats();

        $fileExtension = pathinfo($file->getTitle(), PATHINFO_EXTENSION);

        $format = $formats[$fileExtension];
        if (empty($format)) {
            $this->logger->error("Format does not support: " . $fileId);
            return ["error" => "Format does not support"];
        }

        $params = [];
        $params["type"] = "desktop";

        $params["documentType"] = $format["type"];

        $params["document"]["url"] = $this->GetDownloadUrl($fileId);
        $params["document"]["fileType"] = $fileExtension;
        $params["document"]["key"] = FileUtility::GetKey($file);
        $params["document"]["title"] = $file->getTitle();

        $params["editorConfig"]["lang"] = $user->getLanguageID();
        $params["editorConfig"]["user"] = [
            "id" => $userId,
            "name" => $userName
        ];

        //necessary to do checking file permissions
        $haveEditPermissions = true;

        $canEdit = isset($format['edit']) && $format['edit'];

        if ($haveEditPermissions && $canEdit) {
            $params["document"]["permissions"]["edit"] = $haveEditPermissions;
            $params["editorConfig"]["callbackUrl"] = $this->GetCallbackUrl($fileId);
        } else {
            $params["editorConfig"]["mode"] = "view";
        }

        return $params;
    }

    /**
     * Url for download file directly
     *
     * @param int $fileId - file identifier
     */
    private function GetDownloadUrl($fileId): string
    {
        $params = [
            'action' => 'download',
            'fileId' => $fileId
        ];

        $hash = Crypt::GetHash($params);

        return ServerHostname::HTTPSUrl() . "/plugins/onlyoffice/download?hash=" . $hash;
    }

    /**
     * Url for sending status by document server
     *
     * @param int $fileId - file identifier
     */
    private function GetCallbackUrl($fileId): string
    {
        $params = [
            'action' => 'track',
            'fileId' => $fileId
        ];

        $hash = Crypt::GetHash($params);

        return ServerHostname::HTTPSUrl() . "/api/onlyoffice/callback/track/" . $hash;
    }
}
