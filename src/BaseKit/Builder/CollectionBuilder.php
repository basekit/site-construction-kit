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

    public function widget($name, $type, array $values = array())
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

    public function text($content, array $values = array())
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

        return $this->widget($name, 'Widget.Content', $values);
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

    public function image($src, array $values = array())
    {
        $name = $this->getUniqueName('image');

        $values = array_merge(
            $values,
            array(
                'src' => $src
            )
        );

        return $this->widget($name, 'Widget.Image', $values);
    }

    public function columns($columns = 2, array $values = array())
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

        $widget = $this->widget($name, 'Widget.Responsivecolumns', $values);

        $zones = new Collection;
        $widget->addCollection('zones', $zones);

        // Return the special-case ColumnsBuilder object
        return new ColumnsBuilder($columns, $widget);
    }
}
