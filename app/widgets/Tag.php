<?php

require_once ("app/widgets/HTMLElement.php");

class Tag implements HTMLElement
{
    private static $tabOffset = 4;
    private $name;
    private $attributes = array();
    private $content = null;

    public function __construct($name, $attributes=array(), $content=null)
    {
        $this->name = $name;
        $this->setAttributes($attributes);
        $this->setContent($content);
    }

    public function add (HTMLElement $tag)
    {
        if (!is_array($this->content))
            $this->content = array();
        $this->content[] = $tag;
    }

    public function insert (HTMLElement $tag, $position)
    {
        if (!is_array($this->content))
            $this->content = array();
        array_splice($this->content, $position, 0, array($tag));
    }

    public function setAttributes ($attributes)
    {
        $this->attributes = $attributes;
    }

    public function setAttribute ($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    public function setContent ($content)
    {
        $this->content = $content;
    }

    public function toHtml($offset=0)
    {
        $offsetString = str_repeat(" ", $offset);
        $newLine = "\n";
        $html = $offsetString . "<";
        $html .= $this->name;
        foreach ($this->attributes as $key=>$value)
            $html .= " " . $key . "=\"" . $value . "\"";
        
        if ($this->content !== null)
        {
            $html .= ">";
            if (is_array($this->content))
            {
                foreach ($this->content as $childrenTag)
                    $html .= $newLine . $childrenTag->toHtml($offset + Tag::$tabOffset);
                $html .= $newLine . $offsetString;
            }
            else 
            {
                if (strpos($this->content, "\n") !== false)
                {
                    $html .= $newLine . $this->content . $newLine . $offsetString;
                }
                else
                {
                    $html .= $this->content;
                }
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
