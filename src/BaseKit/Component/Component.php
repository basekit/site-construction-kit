<?php
namespace BaseKit\Component;

abstract class Component
{
    protected $collections = array();

    protected $collectionName;

    protected $ref;

    protected $id;

    protected $position;

    protected $type;

    protected $preset;

    protected $presetRef;

    protected $libraryItemRef;

    protected $pageRef;

    protected $data;

    protected $name;

    protected $className;

    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    public function getRef()
    {
        return $this->ref;
    }

    public function getCollections()
    {
        return $this->collections;
    }

    public function addCollections(array $collections)
    {
        $this->collections = $collections;
    }

    public function addCollection($name, Collection $collection)
    {
        $this->collections[$name] = $collection;
    }

    public function getCollection($name)
    {
        if (isset($this->collections[$name])) {
            return $this->collections[$name];
        }

        return null;
    }

    public function getCollectionName()
    {
        return $this->collectionName;
    }

    public function setCollectionName($collectionName)
    {
        $this->collectionName = $collectionName;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getFullId()
    {
        return sprintf('%s-%s__%s', $this->id, $this->collectionName, $this->name);
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setClassName($className)
    {
        $this->className = $className;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function setPreset($preset)
    {
        $this->preset = $preset;
    }

    public function getPreset()
    {
        return $this->preset;
    }

    public function setPresetRef($presetRef)
    {
        $this->presetRef = $presetRef;
    }

    public function getPresetRef()
    {
        return $this->presetRef;
    }

    public function setLibraryItemRef($libraryItemRef)
    {
        $this->libraryItemRef = $libraryItemRef;
    }

    public function getLibraryItemRef()
    {
        return $this->libraryItemRef;
    }

    public function setPageRef($pageRef)
    {
        $this->pageRef = $pageRef;
    }

    public function getPageRef()
    {
        return $this->pageRef;
    }

    public function setData($data)
    {
        $this->data = $data;
    }
}
