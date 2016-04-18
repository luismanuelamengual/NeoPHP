<?php

namespace NeoPHP\web\html;

class HTMLTag implements HTMLElement
{
    private static $OFFSET = 4;
    private $name;
    private $attributes;
    private $content;

    public function __construct($name, $attributes=null, $content=null)
    {
        $this->name = $name;
        if ($content === null && (!is_array($attributes) || is_numeric(key($attributes))))
        {
            $this->setAttributes(array());
            $this->setContent($attributes);
        }
        else
        {
            $this->setAttributes($attributes);
            $this->setContent($content);
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

    public function setContent ($content)
    {
        $this->content = array();
        if ($content !== null)
        {
            if (is_array($content))
            {
                foreach ($content as $contentElement)
                    $this->add ($contentElement);
            }
            else
            {
                $this->add ($content);
            }
        }
    }
    
    public function getContent ()
    {
        return $this->content;
    }
    
    public function add ($element)
    {
        $this->insert ($element, sizeof($this->content));
    }

    public function insert ($element, $position)
    {
        array_splice($this->content, $position, 0, array($element));
    }

    public function toHtml($offset=0)
    {
        $offsetString = str_repeat(" ", $offset);
        $newLine = "\n";
        $html = $offsetString . "<";
        $html .= $this->name;
        foreach ($this->attributes as $key=>$value)
            $html .= " " . $key . "=\"" . htmlspecialchars($value) . "\"";
        
        $contentSize = sizeof($this->content);
        if ($contentSize > 0)
        {
            $html .= ">";
            if ($contentSize == 1 && !($this->content[0] instanceof HTMLElement))
            {
                $html .= strval($this->content[0]);
            }
            else
            {
                foreach ($this->content as $childItem)
                {
                    if ($childItem instanceof HTMLElement)
                    {
                        $html .= $newLine . $childItem->toHtml($offset + self::$OFFSET);
                    }
                    else
                    {
                        if ($contentSize > 0)
                            $html .= $newLine . str_repeat(" ", $offset + self::$OFFSET);
                        $html .= strval($childItem);
                    }
                }
                $html .= $newLine . $offsetString;
            }
            $html .= "</" . $this->name . ">";
        }
        else
        {
            $html .= " />";
        }
        return $html;
    }
    
    public function __toString()
    {
        return $this->toHtml();
    }
}

?>