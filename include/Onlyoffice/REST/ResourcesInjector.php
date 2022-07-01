<?php

namespace Tuleap\Onlyoffice\REST;

use Luracast\Restler\Restler;

use Tuleap\Onlyoffice\REST\v1\EditorResource;
use Tuleap\Onlyoffice\REST\v1\CallbackResource;

class ResourcesInjector
{
    public function populate(Restler $restler)
    {
        $restler->addAPIClass(
            EditorResource::class,
            'onlyoffice/editor'
        );

        $restler->addAPIClass(
            CallbackResource::class,
            'onlyoffice/callback'
        );
    }
}
