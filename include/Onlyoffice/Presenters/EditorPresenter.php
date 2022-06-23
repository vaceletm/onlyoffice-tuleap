<?php

declare(strict_types=1);

namespace Tuleap\Onlyoffice\Presenters;

class EditorPresenter
{
    public function __construct(
        string $documentServerUrl,
        int $fileId
    ) {
        $this->documentServerUrl = $documentServerUrl;
        $this->fileId = $fileId;
    }
}