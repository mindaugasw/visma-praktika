<?php

namespace App\Service\DIContainer\Config;

use App\Service\PsrLogger\Logger;
use Psr\Log\LoggerInterface;

class ContainerConfig
{
    /**
     * Get list of types that should always be replaced by other types.
     * E.g. what concrete types should be used in place of interfaces.
     *
     * @return string[] [changeable type => type to change with, ... ]
     */
    public function getTypeSubstitutionConfig(): array
    {
        return [
            LoggerInterface::class => Logger::class,
        ];
    }
}
