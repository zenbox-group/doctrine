<?php

namespace ZenBox\Doctrine;

use IteratorAggregate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ZenBox\Doctrine\Extractor\ExtractingIterator;
use ZenBox\Doctrine\Extractor\ExtractorInterface;

class DataProvider implements IteratorAggregate
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var int
     */
    private $page;

    /**
     * @var int
     */
    private $perPage;

    /**
     * @var ExtractorInterface|null
     */
    private $extractor;

    public function __construct(Collection $collection, ExtractorInterface $extractor = null)
    {
        $this->collection = $collection;
        $this->extractor = $extractor;

        filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);

        $this->page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $this->perPage = filter_input(INPUT_GET, 'per-page', FILTER_VALIDATE_INT) ?: 20;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage(int $page)
    {
        $this->page = $page;
    }

    /**
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * @param int $limit
     */
    public function setPerPage(int $limit)
    {
        $this->perPage = $limit;
    }

    /**
     * @return int
     */
    public function getPageCount(): int
    {
        return ceil($this->count() / $this->getPerPage());
    }

    public function getCollection(): Collection
    {
        return $this->collection;
    }

    /**
     * @return Collection
     */
    public function getIterator(): Collection
    {
        $offset = ($this->getPage() - 1) * $this->getPerPage();
        $length = $this->getPerPage();

        return new ArrayCollection($this->collection->slice($offset, $length));
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return iterator_to_array($this->getIterator(), false);
    }

    /**
     * @return array
     */
    public function extract(): array
    {
        if ($this->extractor) {
            return iterator_to_array(new ExtractingIterator($this->getIterator(), $this->extractor), false);
        } else {
            throw new \RuntimeException('Need configure Extractor');
        }
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->collection->count();
    }
}