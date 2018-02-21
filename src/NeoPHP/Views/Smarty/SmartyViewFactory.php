<?php

namespace NeoPHP\Views\Smarty;

use NeoPHP\Views\View;
use NeoPHP\Views\ViewFactory;

class SmartyViewFactory extends ViewFactory {

    private $templatesPath;
    private $compiledTemplatesPath;
    private $configPath;
    private $cachePath;
    private $useCache = false;

    public function __construct(array $config = []) {
        $this->templatesPath = isset($config["templatesPath"]) ? $config["templatesPath"] : getApp()->getResourcesPath() . DIRECTORY_SEPARATOR . "views";
        $this->compiledTemplatesPath = isset($config["compiledTemplatesPath"]) ? $config["compiledTemplatesPath"] : getApp()->getStoragePath() . DIRECTORY_SEPARATOR . "framework" . DIRECTORY_SEPARATOR . "views";
        if (isset($config["configPath"])) {
            $this->configPath = $config["configPath"];
        }
        if (isset($config["cachePath"])) {
            $this->cachePath = $config["cachePath"];
        }
        if (isset($config["useCache"])) {
            $this->useCache = $config["useCache"];
        }
    }

    public function createView($name, array $parameters = []): View {
        $smarty = new \Smarty();
        $smarty->template_dir = $this->templatesPath;
        $smarty->compile_dir = $this->compiledTemplatesPath;
        $smarty->config_dir = $this->configPath;
        $smarty->cache_dir = $this->cachePath;
        foreach ($parameters as $key=>$value) {
            $smarty->assign($key, $value);
        }
        $filename = str_replace(".", DIRECTORY_SEPARATOR, $name);
        $filename .= ".tpl";
        return new SmartyView($smarty, $filename);
    }
}