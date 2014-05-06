<?php
namespace BaseKitTests\Component;

use BaseKit\Component\Collection;
use BaseKit\Component\Widget;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getObjectByPosition()
    {
        $collection = new Collection;

        $collection[0] = array(
            'name' => 'foo',
            'position' => 1,
        );

        $collection[1] = array(
            'name' => 'bar',
            'position' => 10,
        );

        $collection[2] = array(
            'name' => 'foobar',
            'position' => 20,
        );

        $collection[3] = array(
            'name' => 'foobarbaz',
            'position' => 9,
        );

        $result = $collection->getObjectByPosition(9);

        $this->assertEquals($collection[9], $result);
    }

    /**
     * @test
     */
    public function partition()
    {
        $collection = new Collection;

        $component = new Widget\Widget;
        $component->setCollectionName('foo');
        $component->setPosition(0);
        $collection[0] = $component;

        $component = new Widget\Widget;
        $component->setCollectionName('bar');
        $component->setPosition(0);
        $collection[1] = $component;

        $component = new Widget\Widget;
        $component->setCollectionName('foo');
        $component->setPosition(1);
        $collection[2] = $component;

        $result = $collection->partition(
            function ($element) {
                return $element->getCollectionName();
            }
        );

        $this->assertEquals(2, count($result));
        $this->assertTrue(isset($result['foo']));
        $this->assertEquals(2, count($result['foo']));
        $this->assertTrue(isset($result['bar']));
        $this->assertEquals(1, count($result['bar']));
    }

    /**
     * @test
     */
    public function recursivelyFilterReturnsCorrectComponents()
    {
        $collection = new Collection;

        $widget1 = new Widget\Widget;
        $widget2 = new Widget\Widget;

        $collection[0] = $widget1;

        $childCollection = new Collection;

        $widget1->addCollection('widgets', $childCollection);

        $childCollection[0] = $widget2;

        $p = function () {
            return true;
        };

        $widgets = $collection->recursivelyFilter($p);

        $expected = array(
            $widget1,
            $widget2,
        );

        $this->assertEquals($expected, $widgets);
    }
}
