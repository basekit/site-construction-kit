<?php

namespace BaseKit\Builder;

use BaseKit\Builder\PageBuilder;

class SiteBuilder
{
    protected $pages = array();
    protected $domains = array();
    protected $siteRef = 0;
    protected $brandRef = 0;
    protected $accountHolderRef = 0;

    public function __construct()
    {
    }

    public function getSiteRef()
    {
        return $this->siteRef;
    }

    public function setSiteRef($siteRef)
    {
        $this->siteRef = $siteRef;
    }

    public function getBrandRef()
    {
        return $this->brandRef;
    }

    public function setBrandRef($brandRef)
    {
        $this->brandRef = $brandRef;
    }

    public function getAccountHolderRef()
    {
        return $this->accountHolderRef;
    }

    public function setAccountHolderRef($accountHolderRef)
    {
        $this->accountHolderRef = $accountHolderRef;
    }

    public function getDomains()
    {
        return $this->domains;
    }

    public function mapDomain($domain)
    {
        if (!empty($domain) && !in_array($domain, $this->domains)) {
            array_push($this->domains, $domain);
        }
    }

    public function getPages()
    {
        return $this->pages;
    }

    public function getPage($name)
    {
        if (empty($name) || !isset($this->pages[$name])) {
            return null;
        }

        return $this->pages[$name];
    }

    public function createPage($name, $title)
    {
        if (empty($name)) {
            throw new Exception('Page name must be set');
        }

        if (empty($title)) {
            $title = $name;
        }

        $page = new PageBuilder;
        $page->setName($name);
        $page->setTitle($title);

        $this->pages[$name] = $page;

        return $page;
    }
}