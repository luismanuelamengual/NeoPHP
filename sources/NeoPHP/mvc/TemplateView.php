<?php

namespace NeoPHP\mvc;

use Exception;

class TemplateView extends View
{
    protected $templateName;
    protected $parameters;
    protected $sections;
    protected $sectionsStack;
    protected $templateEngines;
    protected $registeredTemplateEngines;
    
    public function __construct (MVCApplication $application, $templateName, array $parameters = [])
    {
        parent::__construct($application);
        $this->templateName = $templateName;
        $this->parameters = $parameters;
        $this->sections = [];
        $this->sectionsStack = [];
        $this->templateEngines = [];
        $this->registeredTemplateEngines = [];
        $this->registeredTemplateEngines["blade"] = BladeTemplateEngine::class;
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
            $sectionContent = str_replace('@parent', $sectionContent, $this->sections[$sectionName]);
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
    
    protected final function includeTemplate ($templateName, array $parameters = [])
    {
        $templateFilename = null;
        $templateParameters = array_merge($this->parameters, $parameters);
        extract ($templateParameters);
        
        $resourcePaths = $this->application->getResourcePaths();
        $storagePath = $this->application->getStoragePath();
        $templatesCachePath = $storagePath . DIRECTORY_SEPARATOR . "templates";
        
        foreach ($resourcePaths as $resourcePath)
        {
            $templatesPath = $resourcePath . DIRECTORY_SEPARATOR . "templates";
                
            foreach ($this->registeredTemplateEngines as $engineType=>$engineClassName) 
            {
                $engineTemplateFilename = $templatesPath . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $templateName) . "." . $engineType . ".php";
                if (file_exists($engineTemplateFilename))
                {
                    $engineTemplateCacheFilename = $templatesCachePath . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $templateName) . ".php";
                    if (!file_exists($engineTemplateCacheFilename) || (filemtime($engineTemplateFilename) > filemtime($engineTemplateCacheFilename))) 
                    {
                        $contents = file_get_contents($engineTemplateFilename);
                        if (!isset($this->templateEngines[$engineType]))
                            $this->templateEngines[$engineType] = new $engineClassName;
                        $compiledContents = $this->templateEngines[$engineType]->compile($contents);

                        try
                        {
                            $engineTemplateDirname = dirname($engineTemplateCacheFilename);
                            if (!file_exists($engineTemplateDirname))
                                mkdir($engineTemplateDirname, 0777, true);
                            file_put_contents($engineTemplateCacheFilename, $compiledContents); 
                        }
                        catch (Exception $ex)
                        {
                            throw new Exception("Permission denied to create template cache file \"$engineTemplateCacheFilename\"");
                        }
                    }
                    $templateFilename = $engineTemplateCacheFilename;
                    break;
                }
            }
            
            if (!empty($templateFilename))
                break;
        }
        
        if ($templateFilename == null)
        {
            foreach ($resourcePaths as $resourcePath)
            {
                $templatesPath = $resourcePath . DIRECTORY_SEPARATOR . "templates";
                $rawTemplateFilename = $templatesPath . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $templateName) . ".php";
                if (file_exists($rawTemplateFilename))
                {
                    $templateFilename = $rawTemplateFilename;
                    break;
                }
            }
        }
       
        if ($templateFilename == null)
            throw new Exception("Template \"$templateName\" was not found in the resource paths !!");
        
        @include $templateFilename;
    }
    
    protected function onRender()
    {
        $this->includeTemplate($this->templateName);
    }
}