<?php

namespace Tuleap\Onlyoffice;

use Psr\Http\Message\ResponseInterface;
use Tuleap\Http\HTTPFactoryBuilder;
use GuzzleHttp\Client;

use Tuleap\Onlyoffice\AppConfig;

class DocumentService
{
    /**
     * @var AppConfig
     */
    private $appConfig;

    public function __construct (
        AppConfig $appConfig
    ) {
        $this->appConfig = $appConfig;
    }

    /**
     * Request to Document Server with custom options
     *
     * @param string $url Id of the file
     * @param string $method - http method
     */
    public function Request($url, $method = 'GET'): ResponseInterface
    {
        $opt = [];

        if($this->appConfig->GetVerifySelfSignedOff()) {
            $opt['verify'] = false;
        }

        $client = new Client($opt);
        $requestFactory = HTTPFactoryBuilder::requestFactory();
        $request = $requestFactory->createRequest($method, $url);

        return $client->sendRequest($request);
    }
}