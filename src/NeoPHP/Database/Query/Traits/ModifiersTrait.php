<?php

namespace NeoPHP\Database\Query\Traits;

trait ModifiersTrait {

    private $modifiers = [];

    public function getModifiers(): array {
        return $this->modifiers;
    }

    public function setModifiers(array $modifiers) {
        $this->modifiers = $modifiers;
    }

    public function addModifiers(...$modifiers) {
        foreach ($modifiers as $modifier) {
            $this->addModifier($modifier);
        }
    }

    public function addModifier($modifier) {
        $this->modifiers[] = $modifier;
    }
}