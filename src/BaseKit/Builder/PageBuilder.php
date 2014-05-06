<?php

namespace BaseKit\Builder;

use BaseKit\Builder\CollectionBuilder;
use BaseKit\Component\Collection;

class PageBuilder extends CollectionBuilder
{
    protected $widgets = null;
    protected $name = '';
    protected $pageRef = 0;

    public function __construct()
    {
        $this->widgets = new Collection;
        parent::__construct('page-zones__main', 'widgets', $this->widgets);
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setRef($pageRef)
    {
        $this->pageRef = $pageRef;
    }

    public function getRef()
    {
        return $this->pageRef;
    }
}
