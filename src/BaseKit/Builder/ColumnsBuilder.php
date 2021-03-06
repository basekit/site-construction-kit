<?php
namespace BaseKit\Builder;

use BaseKit\Builder\CollectionBuilder;
use BaseKit\Component\Component;
use BaseKit\Component\Collection;
use BaseKit\Component\Widget\Widget;

class ColumnsBuilder
{
    protected $columns = array();

    public function __construct($columns, Widget $widget)
    {
        // Construct an ID to be used by the column widget's collections
        $idPrefix = $widget->getId() . '-' . $widget->getCollectionName() . '__' . $widget->getName();

        $zones = $widget->getCollection('zones');

        // Add column collections to the widget
        for ($column = 0; $column < $columns; ++$column) {
            $name = 'column' . ($column > 0 ? $column : '');
            $id = $idPrefix . '-zones__' . $name;

            // Add a column widget
            $zone = new Widget;
            $zone->setId($idPrefix);
            $zone->setCollectionName('zones');
            $zone->setName($name);
            $zone->setType('Widget.Columns.Column');
            $zone->setPosition($column);

            $zones[$column] = $zone;

            $collection = new Collection;
            $zone->addCollection('widgets', $collection);

            $this->columns[] = new CollectionBuilder($id, 'widgets', $collection, $widget);
        }
    }

    public function getLeftColumn()
    {
        return reset($this->columns);
    }

    public function getMiddleColumn()
    {
        return $this->columns[1];
    }

    public function getRightColumn()
    {
        return end($this->columns);
    }

    public function getColumn($index)
    {
        return $this->columns[$index];
    }
}
