<?php

namespace BaseKit\Builder;

use BaseKit\Builder\CollectionBuilder;
use BaseKit\Component\Collection;

class PageBuilder extends CollectionBuilder
{
    protected $pageRef = 0;
    protected $widgets = null;
    protected $name = null;
    protected $templateType = 'default';

    public function __construct()
    {
        $this->widgets = new Collection;
        parent::__construct('page-zones__main', 'widgets', $this->widgets);
    }

    public function getPageRef()
    {
        return $this->pageRef;
    }

    public function setPageRef($pageRef)
    {
        $this->pageRef = $pageRef;
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

    public function setTemplateType($templateType)
    {
        $this->templateType = $templateType;
    }

    public function getTemplateType()
    {
        return $this->templateType;
    }
}
