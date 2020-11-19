<?php

namespace ZenBox\Doctrine\Test;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use stdClass;
use ZenBox\Doctrine\DataProvider;

class DataProviderTest extends TestCase
{

    public function testGetCollection()
    {
        $dataProvider = new DataProvider($this->getNewCollection());
        $collection = $dataProvider->getCollection();

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertCount(100, $collection);
    }

    public function testCount()
    {
        $dataProvider = new DataProvider($this->getNewCollection());

        $this->assertEquals(100, $dataProvider->count());
    }

    public function testToArray()
    {
        $collection = $this->getNewCollection();
        $dataProvider = new DataProvider($collection);
        $array = $dataProvider->toArray();

        $this->assertIsArray($array);
        $this->assertInstanceOf(stdClass::class, current($array));
    }

    public function testGetIterator()
    {
        $dataProvider = new DataProvider($this->getNewCollection());
        $collection = $dataProvider->getIterator();

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertCount(20, $collection);
    }

    public function testSetPage()
    {
        $dataProvider = new DataProvider($this->getNewCollection());
        $dataProvider->setPage(2);

        $this->assertEquals(2, $dataProvider->getPage());
    }

    public function testGetPageCount()
    {
        $dataProvider = new DataProvider($this->getNewCollection());

        $this->assertEquals(5, $dataProvider->getPageCount());
    }

    public function testExtract()
    {
        $dataProvider = new DataProvider($this->getNewCollection(), new StdClassExtractor());
        $array = $dataProvider->extract();

        $this->assertCount(20, $array);
        $this->assertIsArray(current($array));
    }

    public function testGetPage()
    {
        $dataProvider = new DataProvider($this->getNewCollection());

        $this->assertEquals(1, $dataProvider->getPage());
    }

    public function testSetPerPage()
    {
        $dataProvider = new DataProvider($this->getNewCollection());
        $dataProvider->setPerPage(40);
        $collection = $dataProvider->getIterator();

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertCount(40, $collection);
    }

    public function testGetPerPage()
    {
        $dataProvider = new DataProvider($this->getNewCollection());

        $this->assertEquals(20, $dataProvider->getPerPage());
    }

    private function getNewCollection(): Collection
    {
        return new ArrayCollection(array_fill(0, 100, new stdClass()));
    }
}
