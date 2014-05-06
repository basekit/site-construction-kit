<?php

namespace BaseKit\Builder\Writer;

use BaseKit\Builder\PageBuilder;

interface WriterInterface
{
    public function write(PageBuilder $pageBuilder);
}
