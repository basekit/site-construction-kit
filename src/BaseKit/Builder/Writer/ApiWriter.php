<?php

namespace BaseKit\Builder\Writer;

use BaseKit\Api\Client;
use BaseKit\Builder\SiteBuilder;
use BaseKit\Builder\PageBuilder;
use BaseKit\Component\Collection;
use BaseKit\Builder\AccountHolderBuilder;

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
                'siteType' => 'responsive',
            )
        );

        $response = $createSiteCmd->execute();

        $siteRef = $response['site']['ref'];
        $site->setSiteRef($siteRef);
        $site->setProfileRef($response['site']['profileRef']);

        $updateSiteCmd = $this->apiClient->getCommand(
            'UpdateSite',
            array(
                'siteRef' => $siteRef,
                'templateRef' => $site->getTemplateRef() > 0 ? $site->getTemplateRef() : 7,
            )
        );
        $response = $updateSiteCmd->execute();

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

    public function updateTemplate(SiteBuilder $site)
    {
        $updateSiteCmd = $this->apiClient->getCommand(
            'UpdateSite',
            array(
                'siteRef' => $site->getSiteRef(),
                'templateRef' => $site->getTemplateRef() > 0 ? $site->getTemplateRef() : 7,
            )
        );
        $updateSiteCmd->execute();
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
        $pageData = array(
            'menu' => 1,
            'siteRef' => $siteRef,
            'pageUrl' => $page->getName() == 'home' ? 'temporary' : $page->getName(),
            'seo_title' => $page->getTitle(),
            'status' => 'active',
            'title' => $page->getTitle(),
            'type' => 'page',
            'headscript' => $page->getHeadScript(),
            'templateType' => $page->getTemplateType()
        );

        if ($page->getParentId() > 0) {
            $pageData = array_merge(
                $pageData,
                array(
                    'folder' => $page->getParentId()
                )
            );
        }

        $createPageCmd = $this->apiClient->getCommand(
            'CreateSitePage',
            $pageData
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

        $this->setHiddenTemplateWidgets($page, $siteRef);

        $this->setFeatureImage($page, $siteRef);
    }

    public function writeFolder(PageBuilder $page, $siteRef)
    {
        $createFolderCmd = $this->apiClient->getCommand(
            'CreateSitePage',
            array(
                'menu' => 0,
                'siteRef' => $siteRef,
                'pageUrl' => $page->getName(),
                'seo_title' => '',
                'status' => 'active',
                'title' => $page->getTitle(),
                'type' => 'folder',
                'folder' => 0,
                'headscript' => $page->getHeadScript(),
                'templateType' => 'default'
            )
        );

        $response = $createFolderCmd->execute();

        $folderRef = $response['page']['ref'];
        $parentId = $response['page']['parentId'];

        $page->setPageRef($folderRef);

        $page->setParentId($parentId);

        $page->updateChildParentIds();

        $this->createCollection($page->getCollection(), $siteRef, $folderRef);

        $updatePageCmd = $this->apiClient->getCommand(
            'UpdateSitePage',
            array(
                'siteRef' => $siteRef,
                'pageRef' => $folderRef,
                'title' => $page->getTitle(),
            )
        );

        $updatePageCmd->execute();
    }

    public function writeSite(SiteBuilder $siteBuilder)
    {
        if ($siteBuilder->getSiteRef() === 0) {
            error_log('CREATE');
            $siteRef = $this->createSite($site);
        }

        foreach ($siteBuilder->getPages() as $page) {
            if ($page->getIsFolder()) {
                $this->writeFolder($page, $siteBuilder->getSiteRef());
                $children = $page->getChildPages();
                foreach ($children as $child) {
                    $this->writePage($child, $siteBuilder->getSiteRef());
                }
            } else {
                $this->writePage($page, $siteBuilder->getSiteRef());
            }
        }

        $getSiteCmd = $this->apiClient->getCommand(
            'GetSite',
            array(
                'siteRef' => $siteBuilder->getSiteRef(),
            )
        );

        $site = $getSiteCmd->execute();

        $siteURL =  (!empty($site['site']['primaryUrl']))
                    ? $site['site']['primaryUrl']
                    : current($site['site']['domains']);

        return array(
            'url' => $siteURL,
        );
    }

    public function resetSite(SiteBuilder $site)
    {
        if ($site->getSiteRef() !== null && $site->getSiteRef() > 0) {
            $resetSiteCmd = $this->apiClient->getCommand(
                'ResetSite',
                array(
                    'siteRef' => $site->getSiteRef(),
                )
            );
            $resetSiteCmd->execute();
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
                array_push(
                    $fields,
                    array(
                        'name' => $name,
                        'value' => $value
                    )
                );
            }

            $response = $this->apiClient->put(
                sprintf(
                    'users/%d/profiles/%d',
                    $site->getAccountHolderRef(),
                    $site->getProfileRef()
                ),
                array(
                    'Content-Type' => 'application/json'
                ),
                json_encode(
                    array(
                        'fields' => $fields
                    )
                )
            )->send();
        }
    }

    public function writeAccountHolder(AccountHolderBuilder $accountHolder)
    {
        $createUserCmd = $this->apiClient->getCommand(
            'CreateUser',
            array(
                'username' => $accountHolder->getUsername(),
                'password' => $accountHolder->getPassword(),
                'firstName' => $accountHolder->getFirstName(),
                'lastName' => $accountHolder->getLastName(),
                'email' => $accountHolder->getEmail(),
                'brandRef' => $accountHolder->getBrandRef(),
                'languageCode' => $accountHolder->getLanguageCode(),
            )
        );

        $response = $createUserCmd->execute();
        $accountHolder->setRef($response['accountHolder']['ref']);
    }

    private function setHiddenTemplateWidgets(PageBuilder $page, $siteRef)
    {
        foreach ($page->getHiddenTemplateWidgets() as $staticWidgetId) {
            $updateStaticValuesCmd = $this->apiClient->getCommand(
                'Updatestaticvaluesforastaticwidget',
                array(
                    'siteRef' => $siteRef,
                    'staticWidgetId' => $staticWidgetId,
                    'values' => array(
                        'showTplWidget' => 0,
                    ),
                )
            );

            $updateStaticValuesCmd->execute();
        }
    }

    private function setFeatureImage(PageBuilder $page, $siteRef)
    {
        if (null === ($featureImageUrl = $page->getFeatureImageUrl())) {
            return;
        }

        $updateFeatureImageCmd = $this->apiClient->getCommand(
            'Updatestaticvaluesforastaticwidget',
            array(
                'siteRef' => $siteRef,
                'staticWidgetId' => $page->getFeatureWidgetId(),
                'values' => array(
                    'bgImg' => $page->getFeatureImageUrl(),
                    'useTemplate' => 0,
                    'showTplWidget' => 1,
                    'showBtn' => 0
                ),
            )
        );

        $updateFeatureImageCmd->execute();

        $updateFeatureImageCmd = $this->apiClient->getCommand(
            'Updatestaticvaluesforastaticwidget',
            array(
                'siteRef' => $siteRef,
                'staticWidgetId' => 'logo-logo',
                'values' => array(
                    'bgImg' => $page->getFeatureImageUrl(),
                    'useTemplate' => 0,
                    'showTplWidget' => 1,
                    'showBtn' => 0
                ),
            )
        );

        $updateFeatureImageCmd->execute();
    }
}
