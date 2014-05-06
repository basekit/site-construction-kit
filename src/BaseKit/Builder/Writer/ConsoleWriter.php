<?php

namespace BaseKit\Builder\Writer;

use BaseKit\Builder\PageBuilder;
use BaseKit\Component\Collection;

class ConsoleWriter implements WriterInterface
{
    private $siteRef = 0;

    public function createSite()
    {
        $this->siteRef = rand(1, 100);

        print("Site: ref = {$this->siteRef}" . PHP_EOL);
    }

    public function write(PageBuilder $page)
    {
        print("Page: ref = {$page->getRef()}, name = {$page->getName()}, title = {$page->getTitle()}" . PHP_EOL);
        $this->writeCollection($page->getCollection(), $page);
    }

    private function writeCollection(Collection $collection, PageBuilder $page)
    {
        foreach ($collection as $widget) {

            print("Widget: id = {$widget->getId()}, name = {$widget->getName()}, type = {$widget->getType()}, position = {$widget->getPosition()}" . PHP_EOL);

            foreach ($widget->getValues() as $name => $value) {
                print("    {$name} = {$value}" . PHP_EOL);
            }

            foreach ($widget->getCollections() as $collection) {
                $this->writeCollection($collection, $page);
            }
        }
    }
}
