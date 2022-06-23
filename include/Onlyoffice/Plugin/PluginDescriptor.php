<?php

namespace Tuleap\Onlyoffice\Plugin;

class PluginDescriptor extends \PluginDescriptor
{
    public function __construct()
    {
        parent::__construct(
            'ONLYOFFICE',
            false,
            dgettext('tuleap-onlyoffice', 'ONLYOFFICE app description')
        );
    }
}
