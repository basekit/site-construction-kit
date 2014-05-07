<?php

namespace BaseKit\Builder\Writer;

use BaseKit\Builder\PageBuilder;
use BaseKit\Builder\SiteBuilder;

interface WriterInterface
{
    public function writePage(PageBuilder $page, $siteRef);
    public function writeSite(SiteBuilder $site);
}
