<?php

namespace BaseKit\Builder;

use BaseKit\Component\Widget\Widget;
use BaseKit\Component\Collection;

class CollectionBuilder
{
    protected $id;
    protected $collectionName;
    protected $collection;
    protected $parent;
    protected $footerContent;
    protected $footerId;
    protected $logoImageId = 'logo-logo';
    protected $logoImageUrl = null;
    protected $featureImageUrl = null;
    protected $featureWidgetId = 'feature-featurehome';
    protected $hiddenTemplateWidgets = array();

    public function __construct($id, $collectionName, Collection $collection, Widget $parent = null)
    {
        $this->id = $id;
        $this->collectionName = $collectionName;
        $this->collection = $collection;
        $this->parent = $parent;
    }

    public function getCollection()
    {
        return $this->collection;
    }

    public function addWidget($name, $type, array $values = array())
    {
        // Create the widget
        $widget = new Widget;
        $widget->setId($this->id);
        $widget->setCollectionName($this->collectionName);
        $widget->setName($name);
        $widget->setType(strtolower($type));
        $widget->setPosition(count($this->collection));
        $widget->setValues($values);
        $widget->setClassName(str_replace('widget.', '', strtolower($type)));

        if ($this->parent !== null && $this->parent->getType() == 'widget.responsivecolumns') {
            $this->parent->setValues(
                array_merge(
                    $this->parent->getValues(),
                    array('isEmpty' => 0)
                )
            );
        }

        // Append the widget to the zone's collection
        $this->collection[count($this->collection)] = $widget;

        return $widget;
    }

    public function getUniqueName($name, $suffix = 1)
    {
        foreach ($this->collection as $widget) {
            if ($widget->getName() == $name . $suffix) {
                ++$suffix;
            }
        }

        return $name . $suffix;
    }

    public function addText($content, array $values = array())
    {
        $name = $this->getUniqueName('text');

        $values = array_merge(
            $values,
            array(
                'content' => $content,
                'type' => 'widget.content',
                'localClass' => $this->generateLocalClass('widget.content'),
            )
        );

        return $this->addWidget($name, 'Widget.Content', $values);
    }

    public function addGallery($title = '', $images = array(), array $values = array())
    {
        $name = $this->getUniqueName('gallery');

        $values = array_merge(
            $values,
            array(
                'images' => $images,
                'type' => 'widget.gallery',
                'imageScale' => 'original',
                'showTitle' =>  1,
                'showDescription' => 1,
                'title' => $title,
                'localClass' => $this->generateLocalClass('widget.gallery'),
            )
        );

        return $this->addWidget($name, 'Widget.Gallery', $values);
    }

    public function addFooter($id, $content)
    {
        $this->setFooterId($id);
        $this->setFooterContent($content);
    }

    private function generateLocalClass($type)
    {
        $randomCharacters = '';
        for ($ii = 0; $ii < 6; $ii++) {
            $d = rand(1, 30) % 2 ;
            $randomCharacters .= ($d ? chr(rand(65, 90)) : chr(rand(48, 57)));
        }
        return str_replace('.', '-', strtolower($type)) . '-' . $randomCharacters;
    }

    public function addImage($src, $align = 'center', array $values = array())
    {
        $name = $this->getUniqueName('image');

        $values = array_merge(
            $values,
            array(
                'align' => 'widget-align-' . $align,
                'src' => $src
            )
        );

        return $this->addWidget($name, 'Widget.Image', $values);
    }

    public function addColumns($columns = 2, array $values = array())
    {
        $name = $this->getUniqueName('responsivecolumns');

        if ($columns < 2 || $columns > 4) {
            throw new \Exception('Responsive columns only supports 2, 3 or 4 columns');
        }

        $columnsPreset = array(
            2 => 'columns-two-50-50',
            3 => 'columns-three-33-34-33',
            4 => 'columns-four-25-25-25-25'
        );

        $values = array_merge(
            $values,
            array(
                'columns' => $columns,
                'preset' => $columnsPreset[$columns],
                'isEmpty' => 1
            )
        );

        $widget = $this->addWidget($name, 'Widget.Responsivecolumns', $values);

        $zones = new Collection;
        $widget->addCollection('zones', $zones);

        // Return the special-case ColumnsBuilder object
        return new ColumnsBuilder($columns, $widget);
    }

    public function addMap(array $values = array())
    {
        $name = $this->getUniqueName('map');

        return $this->addWidget($name, 'Widget.Map', $values);
    }

    public function addContactForm(array $values = array())
    {
        $name = $this->getUniqueName('contactform');

        return $this->addWidget($name, 'Widget.Contactform', $values);
    }

    public function addSpace($height, array $values = array())
    {
        $name = $this->getUniqueName('space');

        $values = array_merge(
            $values,
            array(
                'height' => $height
            )
        );

        return $this->addWidget($name, 'Widget.Space', $values);
    }

    public function setHiddenTemplateWidgets($hiddenTemplateWidgets)
    {
        $this->hiddenTemplateWidgets = $hiddenTemplateWidgets;
    }

    public function getHiddenTemplateWidgets()
    {
        return $this->hiddenTemplateWidgets;
    }

    public function setFeatureWidgetId($featureWidgetId)
    {
        $this->featureWidgetId = $featureWidgetId;
    }

    public function getFeatureWidgetId()
    {
        return $this->featureWidgetId;
    }

    public function setFeatureImageUrl($featureImageUrl)
    {
        $this->featureImageUrl = $featureImageUrl;
    }

    public function getFeatureImageUrl()
    {
        return $this->featureImageUrl;
    }

    public function setLogoWidgetId($logoImageId)
    {
        $this->logoImageId = $logoImageId;
    }

    public function getLogoWidgetId()
    {
        return $this->logoImageId;
    }

    public function setLogoImageUrl($logoImageUrl)
    {
        $this->logoImageUrl = $logoImageUrl;
    }

    public function getLogoImageUrl()
    {
        return $this->logoImageUrl;
    }

    public function setFooterContent($footerContent)
    {
        $this->footerContent = $footerContent;
    }

    public function getFooterContent()
    {
        return $this->footerContent;
    }

    public function setFooterId($footerId)
    {
        $this->footerId = $footerId;
    }

    public function getFooterId()
    {
        return $this->footerId;
    }
}
