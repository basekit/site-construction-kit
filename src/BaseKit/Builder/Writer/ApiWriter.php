<?php

namespace BaseKit\Builder\Writer;

use BaseKit\Api\Client;
use BaseKit\Builder\SiteBuilder;
use BaseKit\Builder\PageBuilder;
use BaseKit\Component\Collection;

class ApiWriter implements WriterInterface
{
    private $apiClient = null;

    public function setApiClient(Client $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function createSite(SiteBuilder $site)
    {
        $domains = $site->getDomains();

        if (empty($domains)) {
            throw new Exception('Site has no domains');
        }

        $primaryDomain = array_shift($domains);

        $createSiteCmd = $this->apiClient->getCommand(
            'CreateSite',
            array(
                'brandRef' => $site->getBrandRef(),
                'accountHolderRef' => $site->getAccountHolderRef(),
                'domain' => $primaryDomain,
                'type' => 'responsive'
            )
        );

        $response = $createSiteCmd->execute();

        $siteRef = $response['site']['ref'];
        $site->setSiteRef($siteRef);

        foreach ($domains as $domain) {
            $mapDomainCmd = $this->apiClient->getCommand(
                'MapDomain',
                array(
                    'siteRef' => $siteRef,
                    'domain' => $domain
                )
            );

            $response = $mapDomainCmd->execute();
        }
    }

    private function createCollection(Collection $collection, $siteRef, $pageRef)
    {
        foreach ($collection as $widget) {
            $addWidgetCmd = $this->apiClient->getCommand(
                'AddWidgetToPage',
                array(
                    'siteRef' => $siteRef,
                    'pageRef' => $pageRef,
                    'parentId' => $widget->getId(),
                    'position' => $widget->getPosition(),
                    'collection' => $widget->getCollectionName(),
                    'type' => $widget->getType(),
                    'name' => $widget->getName(),
                    'libraryItemRef' => 0,
                    'templateRef' => 0,
                    'values' => $widget->getValues()
                )
            );

            $response = $addWidgetCmd->execute();

            $widgetRef = $response['widget']['ref'];

            foreach ($widget->getCollections() as $collection) {
                $this->createCollection($collection, $siteRef, $pageRef);
            }
        }
    }

    public function writePage(PageBuilder $page, $siteRef)
    {
        $createPageCmd = $this->apiClient->getCommand(
            'CreateSitePage',
            array(
                'menu' => 1,
                'siteRef' => $siteRef,
                'pageUrl' => $page->getName() . 'x', // @todo: remove default home page
                'seo_title' => $page->getTitle(),
                'status' => 'active',
                'title' => $page->getTitle(),
                'type' => 'page'
            )
        );

        $response = $createPageCmd->execute();
        $pageRef = $response['page']['ref'];
        $page->setPageRef($pageRef);

        $this->createCollection($page->getCollection(), $siteRef, $pageRef);
    }

    public function writeSite(SiteBuilder $site)
    {
        if ($site->getSiteRef() === 0) {
            $this->createSite($site);
        }

        foreach ($site->getPages() as $page) {
            $this->writePage($page, $site->getSiteRef());
        }
    }
}
