<?php
namespace BaseKit\Component;

class Collection implements \ArrayAccess, \IteratorAggregate, \Countable
{
    protected $elements = array();

    public function __construct(array $elements = array())
    {
        $this->elements = $elements;
    }

    public function offsetExists($offset)
    {
        return isset($this->elements[$offset]);
    }

    public function offsetGet($offset)
    {
        if (isset($this->elements[$offset])) {
            return $this->elements[$offset];
        }

        return null;
    }

    public function offsetSet($offset, $value)
    {
        $this->elements[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->elements[$offset]);
    }

    public function getObjectByName($name)
    {
        $collection = $this->filter(
            function ($element) use ($name) {
                return $element->getName() == $name;
            }
        );

        if (count($collection) != 1) {
            return null;
        }

        return $collection->current();
    }

    public function current()
    {
        return current($this->elements);
    }

    public function getObjectByPosition($position)
    {
        if (isset($this->elements[$position])) {
            return $this->elements[$position];
        }

        return null;
    }

    public function getObjectById($id)
    {
        $collection = $this->filter(
            function ($element) use ($id) {
                return $element->getFullId() == $id;
            }
        );

        if (count($collection) != 1) {
            return null;
        }

        return $collection->current();
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->elements);
    }

    public function filter(\Closure $p)
    {
        return new static(array_filter($this->elements, $p));
    }

    public function toArray()
    {
        return $this->elements;
    }

    public function count()
    {
        return count($this->elements);
    }

    /**
     * @param \Closure $p
     */
    public function partition(\Closure $p)
    {
        $collections = array();

        foreach ($this->elements as $key => $element) {
            if (!isset($collections[$p($element)])) {
                $collections[$p($element)] = new static();
            }

            $collections[$p($element)][$key] = $element;
        }

        return $collections;
    }

    public function ksort()
    {
        ksort($this->elements);
    }

    public function recursivelyFilter($p, $collection = null)
    {
        $widgets = array();

        if (null === $collection) {
            $collection = $this;
        }

        $this->ksort();

        foreach ($collection as $component) {
            if ($p($component)) {
                $widgets[] = $component;
            }

            foreach ($component->getCollections() as $coll) {
                $widgets = array_merge($widgets, $this->recursivelyFilter($p, $coll));
            }
        }

        return $widgets;
    }

    public function keys()
    {
        return array_keys($this->elements);
    }
}
