<?php

namespace NeoPHP\util\formatting;

use NeoPHP\util\IntrospectionUtils;
use ReflectionClass;
use stdClass;

final class CSVFormatter
{
    private $fields;
    private $separator;
    
    public function __construct ($entitiesClassName=null)
    {
        $this->fields = array();
        $this->separator = ";";
        if (!empty($entitiesClassName))
            $this->addFieldsFromClassName ($entitiesClassName);
    }
    
    public function addField ($field, $fieldHeader=null, callable $fieldRenderer=null)
    {
        if (empty($fieldHeader))
            $fieldHeader = $field; 
        $this->fields[$field] = new stdClass();
        $this->fields[$field]->header = $fieldHeader;
        $this->fields[$field]->renderer = $fieldRenderer;
    }
    
    public function addFieldsFromClassName ($className)
    {
        $classData = new ReflectionClass($className);
        $classProperties = $classData->getProperties();
        foreach ($classProperties as $property)
            $this->addField($property->getName());
    }
    
    public function clearFields ()
    {
        $this->fields = array();
    }
    
    public function setSeparator ($separator)
    {
        $this->separator = $separator;
    }
    
    public function getSeparator ()
    {
        return $this->separator;
    }
    
    public function format (array $entities)
    {
        if (sizeof($this->fields) == 0 && sizeof($entities) > 0)
            $this->addFieldsFromClassName (get_class($entities[0]));
        
        $csv = "";
        foreach ($this->fields as $fieldData)
        {
            $csv .= '"' . $fieldData->header . '"';
            $csv .= $this->separator;
        }
        $csv .= "\r\n";
        foreach ($entities as $entity)
        {
            foreach ($this->fields as $field=>$fieldData)
            {
                $value = IntrospectionUtils::getRecursivePropertyValue($entity, $field);
                if ($fieldData->renderer != null)
                    $value = call_user_func ($fieldData->renderer, $value);
                $csv .= '"' . $value . '"';
                $csv .= $this->separator;
            }
            $csv .= "\r\n";
        }
        return $csv;
    }
}

?>