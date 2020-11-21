<?php

declare(strict_types=1);

namespace ZenBox\Doctrine\Console;

use Doctrine\Migrations\Configuration\Exception\ConfigurationException;
use LogicException;
use function sprintf;

final class InvalidConfigurationKey extends LogicException implements ConfigurationException
{
    public static function new(string $key): self
    {
        return new self(sprintf('Invalid configuration. Configuration key "%s" does not exist.', $key));
    }
}
