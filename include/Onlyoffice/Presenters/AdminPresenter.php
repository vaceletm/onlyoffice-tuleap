<?php

declare(strict_types=1);

namespace Tuleap\Onlyoffice\Presenters;

use CSRFSynchronizerToken;

class AdminPresenter
{
    /**
     * @var CSRFSynchronizerToken
     */
    public $csrf_token;

    /**
     * @var string
     */
    public $documentServerUrl;

    /**
     * @var bool
     */
    public $verifySelfSigned;

    /**
     * @var string
     */
    public $jwtSecret;

    public function __construct(
        CSRFSynchronizerToken $csrf_token,
        string $documentServerUrl,
        bool $verifySelfSigned,
        string $jwtSecret
    ) {
        $this->csrf_token = $csrf_token;
        $this->documentServerUrl = $documentServerUrl;
        $this->verifySelfSigned = $verifySelfSigned;
        $this->jwtSecret = $jwtSecret;
    }
}