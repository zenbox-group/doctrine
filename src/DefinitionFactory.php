<?php

namespace ZenBox\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

class DefinitionFactory
{
    /**
     * @param $className
     * @return \Closure
     */
    public static function create($className) {
        return function (ContainerInterface $container) use ($className) {
            return $container->get(EntityManagerInterface::class)->getRepository($className);
        };
    }
}