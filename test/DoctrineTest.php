<?php

namespace PsfPro\Math\Test;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use ZenBox\Doctrine\DataProvider;

class DoctrineTest extends TestCase
{
    public function testDataProvider()
    {
        $collection = new ArrayCollection([
            new \stdClass(),
            new \stdClass(),
            new \stdClass(),
        ]);
        $dataProvider = new DataProvider($collection);

        $this->assertEquals(3, $dataProvider->count());
    }
}
