<?php

require_once ("app/widgets/html/RawHTMLElement.php");

class Tag implements HTMLElement
{
    private static $tabOffset = 4;
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

    public function setAttributes ($attributes)
    {
        $this->attributes = (is_array($attributes))? $attributes : array();
    }

    public function setAttribute ($key, $value)
    {
        $this->attributes[$key] = $value;
    }
    
    public function getAttribute ($key)
    {
        return $this->attributes[$key];
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
    
    public function add ($tag)
    {
        $this->insert ($tag, sizeof($this->content));
    }

    public function insert ($tag, $position)
    {
        if (is_string($tag))
            $tag = new RawHTMLElement($tag);   
        array_splice($this->content, $position, 0, array($tag));
    }

    public function toHtml($offset=0)
    {
        $offsetString = str_repeat(" ", $offset);
        $newLine = "\n";
        $html = $offsetString . "<";
        $html .= $this->name;
        foreach ($this->attributes as $key=>$value)
            $html .= " " . $key . "=\"" . $value . "\"";
        
        if (sizeof($this->content) > 0)
        {
            $html .= ">";
            if ((sizeof($this->content) == 1) && ($this->content[0] instanceof RawHTMLElement))
            {
                $contentString = $this->content[0]->getHtml();
                if (strpos($contentString, "\n") !== false)
                {
                    $html .= $newLine . $contentString . $newLine . $offsetString;
                }
                else
                {
                    $html .= $contentString;
                }
            }
            else
            {
                foreach ($this->content as $childrenTag)
                {
                    if ($childrenTag instanceof HTMLElement)
                        $html .= $newLine . $childrenTag->toHtml($offset + Tag::$tabOffset);
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
}

?>