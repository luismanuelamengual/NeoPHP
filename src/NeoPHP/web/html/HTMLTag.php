<?php

namespace NeoPHP\web\html;

class HTMLTag
{
    private static $OFFSET = 4;
    private $name;
    private $attributes;
    private $value = null;
    private $children = [];
    private $level = 0;

    public function __construct($name, $attributes=null, $content=null)
    {
        if ($content === null && (!is_array($attributes) || is_numeric(key($attributes))))
        {
            list($name, $attributes, $content) = [$name, [], $attributes];
        }
            
        $this->name = $name;
        $this->attributes = $attributes;
        if (is_string($content))
        {
            $this->value = $content;
        }
        else if ($content instanceof HTMLTag)
        {
            $this->add($content);
        }
        else if (is_array($content))
        {
            foreach ($content as $childTag)
            {
                $this->add($childTag);
            }
        }
    }
        
    public function setAttributes (array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function setAttribute ($key, $value)
    {
        $this->attributes[$key] = $value;
    }
    
    public function getAttribute ($key)
    {
        return array_key_exists($key, $this->attributes)? $this->attributes[$key] : null;
    }

    public function setValue ($value)
    {
        $this->value = $value;
    }
    
    public function getValue ()
    {
        return $this->value;
    }
    
    public function getLevel()
    {
        return $this->level;
    }

    public function setLevel($level)
    {
        $this->level = $level;
        foreach ($this->children as $children)
            $children->setLevel($this->level + 1);
    }

    public function add (HTMLTag $element)
    {
        $element->setLevel($this->level + 1);
        $this->children[] = $element;
    }

    public function insert (HTMLTag $element, $position)
    {
        $element->setLevel($this->level + 1);
        array_splice($this->children, $position, 0, array($element));
    }

    public function __toString()
    {
        $offsetString = str_repeat(" ", $this->level * self::$OFFSET);
        $newLine = "\n";
        
        $html = $offsetString . "<";
        $html .= $this->name;
        foreach ($this->attributes as $key=>$value)
            $html .= " " . $key . "=\"" . htmlspecialchars($value) . "\"";
        
        if (sizeof($this->children) > 0 || $this->value != null)
        {
            $html .= ">";
            if ($this->value != null)
            {
                $html .= $this->value;
            }
            else
            {
                foreach ($this->children as $childItem)
                {
                    $html .= $newLine;
                    $html .= $childItem;
                }
                $html .= $newLine . $offsetString;
            }
            $html .= "</" . $this->name . ">";
        }
        else
        {
            $html .= "/>";
        }
        return $html;
    }
}