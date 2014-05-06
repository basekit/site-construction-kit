<?php

namespace BaseKitTests;

use BaseKit\Component\Widget\Widget;
use PHPUnit_Framework_TestCase;

class WidgetTest extends PHPUnit_Framework_TestCase
{

    private $widget;

    public function setUp()
    {
        $this->widget = new Widget;
    }

    /**
     * @test
     */
    public function addTemplateData()
    {
        $this->widget->addTemplateData('example', 'zxcv');
        $templateData = $this->widget->getTemplateData();
        $this->assertEquals('zxcv', $templateData['example']);
    }
}
