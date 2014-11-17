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
                'type' => 'responsive',
                'templateRef' => $site->getTemplateRef() > 0 ? $site->getTemplateRef() : 7
            )
        );

        $response = $createSiteCmd->execute();

        $siteRef = $response['site']['ref'];
        $site->setSiteRef($siteRef);
        $site->setProfileRef($response['site']['profileRef']);

        foreach ($domains as $domain) {
            $mapDomainCmd = $this->apiClient->getCommand(
                'MapDomain',
                array(
                    'siteRef' => $siteRef,
                    'domain' => $domain
                )
            );

            $mapDomainCmd->execute();
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
                'pageUrl' => $page->getName() == 'home' ? 'temporary' : $page->getName(),
                'seo_title' => $page->getTitle(),
                'status' => 'active',
                'title' => $page->getTitle(),
                'type' => 'page'
            )
        );

        $response = $createPageCmd->execute();
        $pageRef = $response['page']['ref'];
        $page->setPageRef($pageRef);

        if ($page->getName() == 'home') {
            // Update the new page to be home page
            $updatePageCmd = $this->apiClient->getCommand(
                'UpdateSitePage',
                array(
                    'siteRef' => $siteRef,
                    'pageRef' => $pageRef,
                    'type' => 'home',
                    'templateType' => 'home'
                )
            );

            $updatePageCmd->execute();

            // Delete the default home page
            $deletePageCmd = $this->apiClient->getCommand(
                'DeleteSitePage',
                array(
                    'siteRef' => $siteRef,
                    'pageRef' => 1
                )
            );

            $deletePageCmd->execute();
        }

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

    public function publishSite(SiteBuilder $site)
    {
        if ($site->getSiteRef() > 0) {
            $publishSiteCmd = $this->apiClient->getCommand(
                'PublishSite',
                array(
                    'siteRef' => $site->getSiteRef(),
                    'comment' => 'Automatic publish'
                )
            );

            $publishSiteCmd->execute();
        }
    }

    public function writeProfile(SiteBuilder $site, array $profileData)
    {
        if ($site->getProfileRef() > 0) {
            $fields = array();

            foreach ($profileData as $name => $value) {
                array_push($fields, array(
                    'name' => $name,
                    'value' => $value
                ));
            }

            $response = $this->apiClient->put(sprintf('users/%d/profiles/%d', $site->getAccountHolderRef(), $site->getProfileRef()), array(
                'Content-Type' => 'application/json'
            ), json_encode(array(
                'fields' => $fields
            )))->send();
        }
    }
}
