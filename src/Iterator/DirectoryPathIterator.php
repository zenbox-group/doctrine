<?php

namespace ZenBox\Doctrine\Iterator;

use FilesystemIterator;
use FilterIterator;

class DirectoryPathIterator extends FilterIterator
{
    public function __construct(string $path)
    {
        parent::__construct(new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS));
    }

    public function current()
    {
        return $this->getInnerIterator()->current()->getRealPath();
    }

    public function accept()
    {
        return $this->getInnerIterator()->current()->isDir();
    }

    public function toArray(): array
    {
        return iterator_to_array($this, false);
    }
}