<?php

namespace ZenBox\Doctrine\Extractor;

interface ExtractorInterface
{
    /**
     * @param object $object
     * @return array
     */
    public function extract($object);
}