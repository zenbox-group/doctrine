<?php

declare(strict_types=1);

namespace ZenBox\Doctrine\Extractor;

interface ExtractorInterface
{
    public function extract(object $object): array;
}
