<?php

namespace NeoPHP\Views\Smarty;

use NeoPHP\Views\View;
use Smarty;

class SmartyView extends View {

    private $smarty;
    private $filename;

    public function __construct(Smarty $smarty, $filename) {
        $this->smarty = $smarty;
        $this->filename = $filename;
    }

    public function set($name, $value) {
        $this->smarty->assign($name, $value);
    }

    public function get($name) {
        $vars = $this->smarty->getConfigVars();
        return $vars[$name];
    }

    protected function renderContent() {
        $this->smarty->display($this->filename);
    }
}