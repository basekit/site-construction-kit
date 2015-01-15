<?php

require(__DIR__ . '/../vendor/autoload.php');

use BaseKit\Api\Client;
use BaseKit\Builder\SiteBuilder;
use BaseKit\Builder\Writer\ApiWriter;

$timestamp = date('YmdHis');

$apiClient = Client::factory(array(
    'base_url' => 'http://rest.basekit.dev',
    'consumer_key' => 'consumer_key',
    'consumer_secret' => 'consumer_secret',
    'token' => 'access_token',
    'token_secret' => 'access_secret'
));

$writer = new ApiWriter;
$writer->setApiClient($apiClient);

$accountHolderBuilder = new AccountHolderBuilder;
$accountHolderBuilder->setBrandRef(1);
$accountHolderBuilder->setUsername('example@example.com');
$accountHolderBuilder->setPassword('myp@ssword');
$accountHolderBuilder->setFirstName('BaseKit');
$accountHolderBuilder->setLastName('User');
$accountHolderBuilder->setEmail('example@example.com');
$accountHolderBuilder->setLanguageCode('en_GB');

$accountHolder = $writer->writeAccountHolder($accountHolderBuilder);

$site = new SiteBuilder;
$site->setBrandRef(1);
$site->setAccountHolderRef($accountHolder->getRef());
$site->mapDomain($timestamp . '-a.basekit.dev');
$site->mapDomain($timestamp . '-b.basekit.dev');
$site->mapDomain($timestamp . '-c.basekit.dev');

$page = $site->createPage('test', 'Test Page', 'default');

$page->addText('<h1>Hello World</h1>');

$columns = $page->addColumns(2);
$columns->getLeftColumn()->addImage('http://placehold.it/200x200');
$columns->getRightColumn()->addImage('http://placehold.it/300x300');

$writer->writeSite($site);

