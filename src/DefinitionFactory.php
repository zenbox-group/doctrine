<?php

declare(strict_types=1);

namespace ZenBox\Doctrine;

use Closure;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

final class DefinitionFactory
{
    public static function create(string $className): Closure
    {
        return function (ContainerInterface $container) use ($className) {
            return $container->get(EntityManagerInterface::class)->getRepository($className);
        };
    }
}
