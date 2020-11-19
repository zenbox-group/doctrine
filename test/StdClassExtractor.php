<?php

declare(strict_types=1);

namespace ZenBox\Doctrine\Test;

use ZenBox\Doctrine\Extractor\ExtractorInterface;

final class StdClassExtractor implements ExtractorInterface
{
    public function extract(object $object): array
    {
        return (array)$object;
    }
}
