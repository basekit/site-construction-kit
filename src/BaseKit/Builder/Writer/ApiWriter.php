<?php

namespace BaseKit\Builder\Writer;

use BaseKit\Api\Client;
use BaseKit\Builder\PageBuilder;
use BaseKit\Component\Collection;

class ApiWriter implements WriterInterface
{
    private $apiClient = null;
    private $siteRef = 0;

    public function setApiClient(Client $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function createSite($brandRef, $accountHolderRef, $domain)
    {
        $createSiteCmd = $this->apiClient->getCommand(
            'CreateSite',
            array(
                'brandRef' => $brandRef,
                'accountHolderRef' => $accountHolderRef,
                'domain' => $domain,
                'type' => 'responsive'
            )
        );

        $response = $createSiteCmd->execute();

        $this->siteRef = $response['site']['ref'];

        print("Site: ref = {$this->siteRef}" . PHP_EOL);
    }

    public function write(PageBuilder $page)
    {
        $createPageCmd = $this->apiClient->getCommand(
            'CreateSitePage',
            array(
                'menu' => 1,
                'siteRef' => $this->siteRef,
                'pageUrl' => $page->getName() . 'x', // @todo: remove default home page
                'seo_title' => $page->getTitle(),
                'status' => 'active',
                'title' => $page->getTitle(),
                'type' => 'page'
            )
        );

        $response = $createPageCmd->execute();

        $page->setRef($response['page']['ref']);

        print("Page: ref = {$page->getRef()}, name = {$page->getName()}, title = {$page->getTitle()}" . PHP_EOL);
        $this->writeCollection($page->getCollection(), $page);
    }

    private function writeCollection(Collection $collection, PageBuilder $page)
    {
        foreach ($collection as $widget) {
            $values = $widget->getValues();
            $collections = $widget->getCollections();

            $addWidgetCmd = $this->apiClient->getCommand(
                'AddWidgetToPage',
                array(
                    'siteRef' => $this->siteRef,
                    'pageRef' => $page->getRef(),
                    'parentId' => $widget->getId(),
                    'position' => $widget->getPosition(),
                    'collection' => $widget->getCollectionName(),
                    'type' => $widget->getType(),
                    'name' => $widget->getName(),
                    'libraryItemRef' => 0,
                    'templateRef' => 0,
                    'values' => $values
                )
            );

            $response = $addWidgetCmd->execute();

            $widgetRef = $response['widget']['ref'];

            print("Widget: ref = {$widgetRef}, name = {$widget->getName()}, type = {$widget->getType()}" . PHP_EOL);

            foreach ($collections as $collection) {
                $this->writeCollection($collection, $page);
            }
        }
    }
}
