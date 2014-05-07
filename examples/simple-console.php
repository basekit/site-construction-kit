<?php

require(__DIR__ . '/../vendor/autoload.php');

use BaseKit\Builder\SiteBuilder;
use BaseKit\Builder\Writer\ConsoleWriter;

$timestamp = date('YmdHis');

$site = new SiteBuilder;
$site->setBrandRef(1);
$site->setAccountHolderRef(1);
$site->mapDomain($timestamp . '-a.basekit.dev');
$site->mapDomain($timestamp . '-b.basekit.dev');
$site->mapDomain($timestamp . '-c.basekit.dev');

$page = $site->createPage('test', 'Test Page');

$page->addText('<h1>Hello World</h1>');

$columns = $page->addColumns(2);
$columns->getLeftColumn()->addImage('http://placehold.it/200x200');
$columns->getRightColumn()->addImage('http://placehold.it/300x300');

$writer = new ConsoleWriter;
$writer->writeSite($site);
