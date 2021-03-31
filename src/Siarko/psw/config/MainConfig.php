<?php

namespace Siarko\psw\config;

use Siarko\cli\bootstrap\Bootstrap;
use Siarko\cli\paradigm\Singleton;
use Siarko\cli\util\config\Config;

class MainConfig extends Config
{
    use Singleton;

    protected function getDefault(): array
    {
        return [
            'docker_dir' => Bootstrap::getHomeDir().'/Projects/local-env-docker'
        ];
    }

    protected function getStructure(): ?array
    {
        return null;
    }

    protected function getPath(): string
    {
        return Bootstrap::getHomeDir().'/.psw/config.cfg';
    }
}