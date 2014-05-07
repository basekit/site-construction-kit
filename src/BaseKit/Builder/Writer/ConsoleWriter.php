<?php

namespace BaseKit\Builder\Writer;

use BaseKit\Builder\PageBuilder;
use BaseKit\Builder\SiteBuilder;
use BaseKit\Component\Collection;

class ConsoleWriter implements WriterInterface
{
    private function writeCollection(Collection $collection, $siteRef, $pageRef)
    {
        foreach ($collection as $widget) {

            print("Widget: name = {$widget->getName()}, type = {$widget->getType()}, values = [" . PHP_EOL);

            foreach ($widget->getValues() as $name => $value) {
                print("    {$name} = {$value}" . PHP_EOL);
            }

            print("]" . PHP_EOL);

            foreach ($widget->getCollections() as $collection) {
                $this->writeCollection($collection, $siteRef, $pageRef);
            }
        }
    }

    public function writePage(PageBuilder $page, $siteRef)
    {
        print("Page: ref = {$page->getPageRef()}, name = {$page->getName()}, title = {$page->getTitle()}" . PHP_EOL);

        $this->writeCollection($page->getCollection(), $siteRef, $page->getPageRef());
    }

    public function writeSite(SiteBuilder $site)
    {
        print("Site: ref = {$site->getSiteRef()}" . PHP_EOL);

        foreach ($site->getPages() as $page) {
            $this->writePage($page, $site->getSiteRef());
        }
    }
}
