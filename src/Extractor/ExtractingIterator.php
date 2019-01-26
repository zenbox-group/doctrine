<?php

namespace ZenBox\Doctrine\Extractor;

class ExtractingIterator extends \IteratorIterator
{
    /**
     * @var ExtractorInterface
     */
    private $extractor;

    /**
     * @param \Traversable $data
     * @param ExtractorInterface $extractor
     */
    public function __construct(\Traversable $data, ExtractorInterface $extractor)
    {
        $this->extractor = $extractor;
        parent::__construct($data);
    }

    /**
     * @return array
     */
    public function current()
    {
        $currentValue = parent::current();

        return $this->extractor->extract($currentValue);
    }
}