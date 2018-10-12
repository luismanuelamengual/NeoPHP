<?php

namespace NeoPHP\Database\Query\Traits;

use NeoPHP\Database\Query\ConditionGroup;

trait WhereConditionsTrait {

    private $whereConditions = null;

    public function hasWhereConditions() {
        return $this->whereConditions != null && !empty($this->whereConditions->getConditions());
    }

    public function whereConditionGroup(ConditionGroup $whereConditions) {
        $this->whereConditions = $whereConditions;
        return $this;
    }

    public function &getWhereConditionGroup () {
        if ($this->whereConditions == null) {
            $this->whereConditions = new ConditionGroup();
        }
        return $this->whereConditions;
    }

    public function whereConnector($connector=null) {
        $this->getWhereConditionGroup()->connector($connector);
        return $this;
    }

    public function where ($field, $operatorOrValue, $value=null) {
        $this->getWhereConditionGroup()->on($field, $operatorOrValue, $value);
        return $this;
    }

    public function whereGroup(ConditionGroup $group) {
        $this->getWhereConditionGroup()->onGroup($group);
        return $this;
    }

    public function whereRaw($sql, array $bindings = []) {
        $this->getWhereConditionGroup()->onRaw($sql, $bindings);
        return $this;
    }

    public function whereField($field, $operatorOrField, $otherField=null) {
        $this->getWhereConditionGroup()->onField($field, $operatorOrField, $otherField);
        return $this;
    }

    public function whereNull($field) {
        $this->getWhereConditionGroup()->onNull($field);
        return $this;
    }

    public function whereNotNull($field) {
        $this->getWhereConditionGroup()->onNotNull($field);
        return $this;
    }

    public function whereIn($field, $value) {
        $this->getWhereConditionGroup()->onIn($field, $value);
        return $this;
    }

    public function whereNotIn($field, $value) {
        $this->getWhereConditionGroup()->onNotIn($field, $value);
        return $this;
    }

    public function whereLike($field, $value, $caseSensitive=false) {
        $this->getWhereConditionGroup()->onLike($field, $value, $caseSensitive);
        return $this;
    }

    public function whereNotLike($field, $value, $caseSensitive=false) {
        $this->getWhereConditionGroup()->onNotLike($field, $value, $caseSensitive);
        return $this;
    }

    /**
     * Retorna la condicion buscada o null si no la encuentra
     * @param string $field
     * @param string $operator
     * @param bool $caseSensitive
     * @param bool $mandatory : indica que la condicion buscada debe ser de tipo obligatoria, esto es:
     * dentro de un conector AND y en primer nivel (no dentro de condiciones anidadas)
     * @return mixed : foundCondition o null si no la encuentra
     */
    public function getWhereCondition ($field, $operator = null, $caseSensitive = true, $mandatory = false) {
        return $this->getWhereConditionGroup()->getCondition($field, $operator, $caseSensitive, $mandatory);
    }

    /**
     * Elimina la condicion buscada y la retorna. Retorna null si no la encuentra
     * @param string $field
     * @param string $operator
     * @param bool $caseSensitive
     * @param bool $mandatory : indica que la condicion buscada debe ser de tipo obligatoria, esto es:
     * dentro de un conector AND y en primer nivel (no dentro de condiciones anidadas)
     * @return mixed : foundCondition o null si no la encuentra
     */
    public function removeWhereCondition ($field, $operator = null, $caseSensitive = true, $mandatory = false) {
        return $this->getWhereConditionGroup()->removeCondition($field, $operator, $caseSensitive, $mandatory);
    }
}