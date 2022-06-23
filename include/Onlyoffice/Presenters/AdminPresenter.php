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

    public function __construct(
        CSRFSynchronizerToken $csrf_token,
        string $documentServerUrl,
        bool $verifySelfSigned
    ) {
        $this->csrf_token = $csrf_token;
        $this->documentServerUrl = $documentServerUrl;
        $this->verifySelfSigned = $verifySelfSigned;
    }
}