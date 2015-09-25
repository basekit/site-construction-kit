<?php

namespace BaseKit\Builder;

use BaseKit\Builder\CollectionBuilder;
use BaseKit\Component\Collection;

class PageBuilder extends CollectionBuilder
{
    protected $isFolder = false;
    protected $childPages = array();
    protected $pageRef = 0;
    protected $parentId = 0;
    protected $widgets = null;
    protected $name = null;
    protected $headScript;
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

    public function getParentId()
    {
        return $this->parentId;
    }

    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
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

    public function setHeadScript($headScript)
    {
        $this->headScript = $headScript;
    }

    public function getHeadScript()
    {
        return $this->headScript;
    }

    public function setIsFolder($isFolder = true)
    {
        $this->isFolder = $isFolder;
    }

    public function getIsFolder()
    {
        return $this->isFolder;
    }

    public function setChildPages($childPages)
    {
        $this->childPages = $childPages;
    }

    public function getChildPages()
    {
        return $this->childPages;
    }

    public function updateChildParentIds()
    {
        if ($this->parentId && !empty($this->childPages)) {
            foreach ($this->childPages as $page) {
                $page->setParentId($this->parentId);
            }
        }
    }
}
