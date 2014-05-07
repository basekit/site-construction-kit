<?php

require(__DIR__ . '/../vendor/autoload.php');

use BaseKit\Api\Client;
use BaseKit\Builder\SiteBuilder;
use BaseKit\Builder\Writer\ApiWriter;

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

$apiClient = Client::factory(array(
    'base_url' => 'http://rest.basekit.dev',
    'consumer_key' => 'consumer_key',
    'consumer_secret' => 'consumer_secret',
    'token' => 'access_token',
    'token_secret' => 'access_secret'
));

$writer = new ApiWriter;
$writer->setApiClient($apiClient);
$writer->writeSite($site);
