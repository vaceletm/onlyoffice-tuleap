<?php

namespace Tuleap\Onlyoffice\Plugin;

class PluginInfo extends \PluginInfo
{
    public function __construct(\Plugin $plugin)
    {
        parent::__construct($plugin);

        $this->setPluginDescriptor(new PluginDescriptor());
    }
}
