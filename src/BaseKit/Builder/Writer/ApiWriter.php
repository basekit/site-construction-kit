<?php

namespace BaseKit\Builder\Writer;

use BaseKit\Api\Client;
use BaseKit\Builder\SiteBuilder;
use BaseKit\Builder\PageBuilder;
use BaseKit\Component\Collection;
use BaseKit\Builder\AccountHolderBuilder;
use Guzzle\Http\Exception\BadResponseException;

class ApiWriter implements WriterInterface
{
    private $apiClient = null;
    private $ignorePageCreationFailures = false;

    public function setApiClient(Client $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function setIgnorePageCreationFailures($ignore)
    {
        $this->ignorePageCreationFailures = (bool) $ignore;
    }

    public function handlePageCreationFailures(\Exception $e)
    {
        if (!$this->ignorePageCreationFailures) {
            throw $e;
        }
    }

    public function createSite(SiteBuilder $site)
    {
        $domains = $site->getDomains();

        if (empty($domains)) {
            throw new \Exception('Site has no domains');
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
            $data = array(
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
            );

            $addWidgetCmd = $this->apiClient->getCommand(
                'AddWidgetToPage',
                $data
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
            'menu'            => 1,
            'status'          => 'active',
            'type'            => 'page',
            'siteRef'         => $siteRef,
            'pageUrl'         => $page->getName() == 'home' ? 'temporary' : $page->getName(),
            'seo_title'       => $page->getSeoTitle(),
            'description'     => $page->getDescription(),
            'title'           => $page->getTitle(),
            'headscript'      => $page->getHeadScript(),
            'templateType'    => $page->getTemplateType()
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

        // Sometimes we have to clear down the widgets
        // created by the content strategy when there
        // is no option to change it to blank on a brand.
        $pageWidgetsCmd = $this->apiClient->getCommand(
            'GetSitesWidgets',
            array(
                'siteRef' => $siteRef,
                'pageRef' => $pageRef,
            )
        );

        $pageWidgets = $pageWidgetsCmd->execute();

        if (count($pageWidgets) > 0 && isset($pageWidgets["widgets"])) {
            foreach ($pageWidgets["widgets"] as $widget) {
                $deleteWidgetCmd = $this->apiClient->getCommand(
                    "DeleteWidget",
                    array(
                        "siteRef"   => (int) $siteRef,
                        "widgetRef" => (int) $widget["ref"],
                    )
                );

                $deleteWidgetResponse = $deleteWidgetCmd->execute();
            }
        }

        if ($page->getName() == 'home') {

            $this->setLogoImage($page, $siteRef);
            $this->setFeatureImage($page, $siteRef);

            $updatePageCmd = $this->apiClient->getCommand(
                'UpdateSitePage',
                array(
                    'siteRef'      => $siteRef,
                    'pageRef'      => $pageRef,
                    'type'         => 'home',
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

            $responce = $deletePageCmd->execute();
        }

        $this->createCollection($page->getCollection(), $siteRef, $pageRef);

        $this->setHiddenTemplateWidgets($page, $siteRef);

        $this->setFooter($page, $siteRef);
    }

    public function writeFolder(PageBuilder $page, $siteRef)
    {
        $data = array(
            'menu'            => 0,
            'siteRef'         => $siteRef,
            'pageUrl'         => $page->getName(),
            'seo_title'       => $page->getSeoTitle(),
            'description'     => $page->getDescription(),
            'status'          => 'active',
            'title'           => $page->getTitle(),
            'type'            => 'folder',
            'folder'          => 0,
            'headscript'      => $page->getHeadScript(),
            'templateType'    => 'default'
        );

        $createFolderCmd = $this->apiClient->getCommand(
            'CreateSitePage',
            $data
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
                'title'   => $page->getTitle(),
            )
        );

        $updatePageCmd->execute();
    }

    public function writeSite(SiteBuilder $siteBuilder)
    {
        if ($siteBuilder->getSiteRef() === 0) {
            $siteRef = $this->createSite($siteBuilder);
        }

        foreach ($siteBuilder->getPages() as $page) {
            if ($page->getIsFolder()) {
                try {
                    $this->writeFolder($page, $siteBuilder->getSiteRef());
                    $children = $page->getChildPages();
                    foreach ($children as $child) {
                        try {
                            $this->writePage($child, $siteBuilder->getSiteRef());
                        } catch (BadResponseException $e) {
                            $this->handlePageCreationFailures($e);
                        }
                    }
                } catch (BadResponseException $e) {
                    $this->handlePageCreationFailures($e);
                }
            } else {
                try {
                    $this->writePage($page, $siteBuilder->getSiteRef());
                } catch (BadResponseException $e) {
                    $this->handlePageCreationFailures($e);
                }
            }
        }

        $getSiteCmd = $this->apiClient->getCommand(
            'GetSite',
            array(
                'siteRef' => $siteBuilder->getSiteRef(),
            )
        );

        $response = $getSiteCmd->execute();

        return $response['site'];
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
                    'bgImg' => $featureImageUrl,
                    'useTemplate' => 0,
                    'showTplWidget' => 1,
                    'showBtn' => 0
                ),
            )
        );

        $response = $updateFeatureImageCmd->execute();
    }

    private function setLogoImage(PageBuilder $page, $siteRef)
    {
        if (null === $page->getLogoWidgetId()) {
            return;
        }

        $updateLogoImageCmd = $this->apiClient->getCommand(
            'Updatestaticvaluesforastaticwidget',
            array(
                'siteRef' => $siteRef,
                'staticWidgetId' => $page->getLogoWidgetId(),
                'values' => array(
                    'useTemplate' => 0,
                    'showTplWidget' => 1,
                ),
            )
        );

        $updateLogoImageCmd->execute();
    }

    private function setFooter(PageBuilder $page, $siteRef)
    {
        if (null === ($footerId = $page->getFooterId())) {
            return;
        }

        $addFooterCmd = $this->apiClient->getCommand(
            'Updatestaticvaluesforastaticwidget',
            array(
                'siteRef' => $siteRef,
                'staticWidgetId' => $footerId,
                'values' => array(
                    'content' => $page->getFooterContent(),
                ),
            )
        );

        $addFooterCmd->execute();
    }

    public function addGlobalValue(SiteBuilder $site, $values)
    {
        if ($site->getSiteRef() !== null && $site->getSiteRef() > 0) {
            $addGlobalValueCmd = $this->apiClient->getCommand(
                'Updateaglobalvalue',
                array_merge(
                    array('siteRef' => $site->getSiteRef()),
                    $values
                )
            );
            $addGlobalValueCmd->execute();
        }
    }
}
