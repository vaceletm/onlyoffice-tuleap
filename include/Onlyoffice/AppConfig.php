<?php

namespace Tuleap\Onlyoffice;

use Tuleap\Config\ConfigKey;
use Tuleap\Config\ConfigDao;

class AppConfig
{
    /**
     * @var ConfigDao
     */
    private $configDao;

    #[ConfigKey("Document server address")]
    private const PLUGIN_ONLYOFFICE_DOCUMENT_SERVER_URL = 'plugin_onlyoffice_document_server_url';

    #[ConfigKey("Verify self signed certificate off")]
    private const PLUGIN_ONLYOFFICE_VERIFY_SELF_SIGNED_OFF = 'plugin_onlyoffice_verify-self-signed-off';

    public function __construct (
        ConfigDao $configDao
    ) {
        $this->configDao = $configDao;
    }

    public function GetDocumentServerUrl(): string
    {
        return (string)\ForgeConfig::get(self::PLUGIN_ONLYOFFICE_DOCUMENT_SERVER_URL);
    }

    public function SetDocumentServerUrl(string $value): void
    {
        $this->configDao->save(self::PLUGIN_ONLYOFFICE_DOCUMENT_SERVER_URL, $value);
    }

    public function GetVerifySelfSignedOff(): bool
    {
        return (bool)\ForgeConfig::get(self::PLUGIN_ONLYOFFICE_VERIFY_SELF_SIGNED_OFF);
    }

    public function SetVerifySelfSignedOff(bool $value): void
    {
        $this->configDao->save(self::PLUGIN_ONLYOFFICE_VERIFY_SELF_SIGNED_OFF, $value);
    }

    public function GetFormats (): array
    {
        return $this->formats;
    }

    private array $formats = [
        "csv" => [ "type" => "cell" ],
        "doc" => [ "type" => "word" ],
        "docm" => [ "type" => "word" ],
        "docx" => [ "type" => "word", "edit" => true ],
        "docxf" => [ "type" => "word", "edit" => true ],
        "oform" => [ "type" => "word", "edit" => true ],
        "dot" => [ "type" => "word" ],
        "dotx" => [ "type" => "word" ],
        "epub" => [ "type" => "word" ],
        "htm" => [ "type" => "word" ],
        "html" => [ "type" => "word" ],
        "odp" => [ "type" => "slide" ],
        "ods" => [ "type" => "cell" ],
        "odt" => [ "type" => "word" ],
        "otp" => [ "type" => "slide" ],
        "ots" => [ "type" => "cell" ],
        "ott" => [ "type" => "word" ],
        "pdf" => [ "type" => "word" ],
        "pot" => [ "type" => "slide" ],
        "potm" => [ "type" => "slide" ],
        "potx" => [ "type" => "slide" ],
        "pps" => [ "type" => "slide" ],
        "ppsm" => [ "type" => "slide" ],
        "ppsx" => [ "type" => "slide", "edit" => true ],
        "ppt" => [ "type" => "slide" ],
        "pptm" => [ "type" => "slide" ],
        "pptx" => [ "type" => "slide", "edit" => true ],
        "rtf" => [ "type" => "word" ],
        "txt" => [ "type" => "word" ],
        "xls" => [ "type" => "cell" ],
        "xlsm" => [ "type" => "cell" ],
        "xlsx" => [ "type" => "cell", "edit" => true ],
        "xlt" => [ "type" => "cell" ],
        "xltm" => [ "type" => "cell" ],
        "xltx" => [ "type" => "cell" ]
    ];
}