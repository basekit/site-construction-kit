<?php

namespace BaseKit\Builder\Writer;

use BaseKit\Builder\PageBuilder;
use BaseKit\Builder\SiteBuilder;
use BaseKit\Builder\AccountHolderBuilder;

interface WriterInterface
{
    public function writeSite(SiteBuilder $site);
    public function writePage(PageBuilder $page, $siteRef, $homePageRef);
    public function writeAccountHolder(AccountHolderBuilder $accountHolder);
}
