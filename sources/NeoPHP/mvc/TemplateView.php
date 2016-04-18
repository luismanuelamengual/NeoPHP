<?php

namespace NeoPHP\mvc;

class TemplateView extends View
{
    protected $templateName;
    protected $parameters;
    protected $sections;
    protected $sectionsStack;
    protected $extensionScripts;
    protected $templateEngines;
    protected $registeredTemplateEngines = ["blade"];
    
    public function __construct($templateName)
    {
        $this->templateName = $templateName;
        $this->parameters = [];
        $this->sections = [];
        $this->sectionsStack = [];
        $this->templateEngines = [];
    }
    
    public function __get($name) 
    {
        return $this->get($name);
    }

    public function __isset($name) 
    {
        return $this->has($name);
    }

    public function __set($name, $value) 
    {
        $this->set($name, $value);
    }
    
    public final function set($name, $value)
    {
        $this->parameters[$name] = $value;   
    }

    public final function get($name)
    {
        return $this->parameters[$name];
    }
    
    public final function has($name)
    {
        return isset($this->parameters[$name]);
    }
   
    protected function getViewsPath ()
    {
        return $this->getApplication()->getResourcesPath() . DIRECTORY_SEPARATOR . "views";
    }
    
    protected function getViewsCachePath ()
    {
        return $this->getApplication()->getStoragePath() . DIRECTORY_SEPARATOR . "framework" . DIRECTORY_SEPARATOR . "views";
    }
    
    protected final function startSection ($sectionName, $sectionContent=null)
    {
        if (!empty($sectionContent))
        {
            $this->extendsection ($sectionName, $sectionContent);
        }
        else
        {
            ob_start() and $this->sectionsStack[] = $sectionName;
        }
    }
    
    protected final function stopSection ($overrite=false)
    {
        $sectionName = array_pop($this->sectionsStack);
        $sectionContent = ob_get_clean();
        if ($overrite)
        {
            $this->sections[$sectionName] = $sectionContent;
        }
        else
        {
            $this->extendSection($sectionName, $sectionContent);
        }
        return $sectionName;
    }
    
    protected final function extendSection ($sectionName, $sectionContent)
    {
        if (isset($this->sections[$sectionName]))
        {
            $sectionContent = str_replace('@parent', $sectionContent, $this->sections[$section]);
        }
        $this->sections[$sectionName] = $sectionContent;
    }
    
    protected final function yieldSection ()
    {
        return $this->yieldContent($this->stopSection());
    }
    
    protected final function yieldContent ($sectionName, $defaultSectionContent=null)
    {
        return isset($this->sections[$sectionName])? $this->sections[$sectionName] : $defaultSectionContent;                
    }
    
    protected final function includeTemplate ($templateName)
    {
        foreach ($this->registeredTemplateEngines as $engineType) 
        {
            $engineTemplateFilename = $this->getViewsPath() . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $templateName) . "." . $engineType . ".php";
            if (file_exists($engineTemplateFilename))
            {
                $engineTemplateCacheFilename = $this->getViewsCachePath() . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $templateName) . ".php";
                if (!file_exists($engineTemplateCacheFilename) || (filemtime($engineTemplateFilename) > filemtime($engineTemplateCacheFilename))) 
                {
                    $contents = file_get_contents($engineTemplateFilename);
                    $engine = $this->getTemplateEngine($engineType);
                    $compiledContents = $engine->compile($contents);
                    file_put_contents($engineTemplateCacheFilename, $compiledContents);
                }
                @include $engineTemplateCacheFilename;
                return;
            }
        }
        
        $templateFilename = $this->getViewsPath() . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $templateName) . ".php";
        @include $templateFilename;
    }
    
    protected function getTemplateEngine ($type)
    {
        if (!isset($this->templateEngines[$type]))
        {
            switch ($type)
            {
                case "blade":
                    $engine = new BladeTemplateEngine();
                    if (method_exists($this, "getBladeExtensions"))
                    {
                        $extensions = $this->getBladeExtensions();
                        foreach ($extensions as $extension)
                            $engine->extend($extension);
                    }
                    $this->templateEngines[$type] = $engine;
                    break;
            }
        }
        return $this->templateEngines[$type];
    }
    
    protected function onRender()
    {
        $this->includeTemplate($this->templateName);
    }
}

?>