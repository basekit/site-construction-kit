<?php
namespace BaseKit\Component\Widget;

class Widget extends \BaseKit\Component\Component
{
    protected $fixed = false;

    protected $type;

    protected $values = array();
    protected $templateData = array();

    public function setFixed($fixed)
    {
        $this->fixed = $fixed;
    }

    public function getFixed()
    {
        return $this->fixed;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getTemplateData()
    {
        $defaultData = array(
            'ref' => $this->getRef(),
            'name' => $this->getName(),
            'fixed' => $this->getFixed(),
            'libraryItemRef' => $this->getLibraryItemRef(),
            'pageRef' => $this->getPageRef(),
            'temporary' => new \stdClass,
            'changed' => new \stdClass,
            'type' => $this->type,
            'data' => $this->values,
            'className' => str_replace('widget.', '', strtolower($this->type)),
            'widget' => $this,
        );

        $completeData = array_merge($this->templateData, $defaultData);
        return $completeData;
    }

    public function toArray()
    {
        return array(
            'ref' => $this->getRef(),
            'name' => $this->getName(),
            'data' => $this->values,
            'type' => $this->type,
        );
    }

    public function getTemplateName()
    {
        $templateName = $this->type;
        $templateName = strtolower($templateName);
        $templateName = str_replace('.', '_', $templateName);
        $templateName = $templateName . '.twig';
        return $templateName;
    }

    public function setValues($values)
    {
        $this->values = $values;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function addTemplateData($name, $value)
    {
        $this->templateData[$name] = $value;
    }
}
