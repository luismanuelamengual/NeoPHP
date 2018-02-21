<?php

namespace NeoPHP\Database\Query\Traits;

trait ModifiersTrait {

    private $modifiers = [];

    public function getModifiers(): array {
        return $this->modifiers;
    }

    public function setModifiers(array $modifiers) {
        $this->modifiers = $modifiers;
        return $this;
    }

    public function addModifiers(...$modifiers) {
        foreach ($modifiers as $modifier) {
            $this->addModifier($modifier);
        }
        return $this;
    }

    public function addModifier($modifier) {
        $this->modifiers[] = $modifier;
        return $this;
    }
}