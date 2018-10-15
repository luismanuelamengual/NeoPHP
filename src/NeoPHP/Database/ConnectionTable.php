<?php

namespace NeoPHP\Database;

use NeoPHP\Database\Query\DeleteQuery;
use NeoPHP\Database\Query\InsertQuery;
use NeoPHP\Database\Query\SelectQuery;
use NeoPHP\Database\Query\Traits\FieldsTrait;
use NeoPHP\Database\Query\Traits\GroupByFieldsTrait;
use NeoPHP\Database\Query\Traits\HavingConditionsTrait;
use NeoPHP\Database\Query\Traits\JoinsTrait;
use NeoPHP\Database\Query\Traits\OrderByFieldsTrait;
use NeoPHP\Database\Query\Traits\SelectFieldsTrait;
use NeoPHP\Database\Query\Traits\SelectModifiersTrait;
use NeoPHP\Database\Query\Traits\TableTrait;
use NeoPHP\Database\Query\Traits\WhereConditionsTrait;
use NeoPHP\Database\Query\UpdateQuery;

/**
 * Class ConnectionTable
 * @package NeoPHP\Database
 */
class ConnectionTable {

    use TableTrait,
        FieldsTrait,
        SelectModifiersTrait,
        SelectFieldsTrait,
        OrderByFieldsTrait,
        GroupByFieldsTrait,
        WhereConditionsTrait,
        HavingConditionsTrait,
        JoinsTrait;

    private $connection;

    /**
     * ConnectionTable constructor.
     * @param $connection
     * @param $table
     */
    public function __construct($connection, $table) {
        $this->table($table);
        $this->connection = $connection;
    }

    /**
     * Obtiene una columna de los resultados en un array
     * @param string $field campo a mostrar en un array
     * @param string $indexField campo a utilizar como indice
     * @return array resultado
     */
    public function pluck($field, $indexField=null) {

        $fieldsFormat = $field;
        $usingFormatting = preg_match_all('/{(\w+\.\w+|\w+)}/', $field, $matches);

        //Obtención del campo/s de la consulta
        $fields = [];
        if ($usingFormatting) {
            $fields = $matches[1];
            foreach ($fields as $formatField) {
                if (($pos = strpos($formatField, '.')) !== false) {
                    $replaceFormatField = substr($formatField, $pos + 1);
                    $fieldsFormat = str_replace($formatField, $replaceFormatField, $fieldsFormat);
                }
            }
        }
        else {
            $fields = [$field];
        }

        //Establecer los campos del select
        $selectFields = $fields;
        if ($indexField != null && !in_array($indexField, $selectFields)) {
            $selectFields[] = $indexField;
        }
        $this->selectFields($selectFields);

        //Obtención del campo de indice
        $returnIndexField = $indexField;
        if (!empty($returnIndexField)) {
            if (($pos = strpos($returnIndexField, '.')) !== false) {
                $returnIndexField = substr($returnIndexField, $pos + 1);
            }
        }

        //Obtención de los campos de retorno
        $returnFields = [];
        foreach ($fields as $returnField) {
            if (($pos = strpos($returnField, '.')) !== false) {
                $returnField = substr($returnField, $pos + 1);
            }
            $returnFields[] = $returnField;
        }

        //Creación del array de resultados
        $fieldResults = [];
        $results = $this->find();
        foreach ($results as $result) {
            $value = null;
            if (!$usingFormatting) {
                $value = $result->{$returnFields[0]};
            }
            else {
                $value = $fieldsFormat;
                foreach ($returnFields as $returnField) {
                    $value = str_replace("{" . $returnField . "}", $result->$returnField, $value);
                }
            }

            if ($returnIndexField != null) {
                $fieldResults[$result->$returnIndexField] = $value;
            }
            else {
                $fieldResults[] = $value;
            }
        }
        return $fieldResults;
    }

    /**
     * Obtiene el primer resultado
     * @return mixed
     */
    public function first() {
        $this->limit(1);
        $results = $this->find();
        return reset($results);
    }

    /**
     * Pagina resultados. Util cuando se tienen que renderizar
     * un gran número de resultados en pantalla y poder hacerlo
     * de manera gradual (por chunks) y ocupando poca cantidad
     * de memoria en cada chunk
     * @param int $size cantidad de resultados por chunk
     * @param callable $clousure funcion a ser llamada con cada chunk
     * @return $this referencia a la Connection table
     */
    public function chunk ($size, callable $clousure) {
        $initialOffset = $this->getOffset();
        $initialLimit = $this->getLimit();
        $position = isset($initialOffset)? $initialOffset : 0;
        while (true) {
            $limit = $size;
            $limitPosition = $position + $size;
            if (!empty($initialLimit) && ($limitPosition > $initialLimit)) {
                $limit = $initialLimit - $position;
            }
            $this->offset($position);
            $this->limit($limit);
            $chunkResults = $this->find();
            if (empty($chunkResults)) {
                break;
            }
            $clousure($chunkResults);
            if (sizeof($chunkResults) < $size) {
                break;
            }
            $position = $limitPosition;
        }
        $this->offset($initialOffset);
        $this->limit($initialLimit);
        return $this;
    }

    /**
     * Obtiene resultados
     * @param $indexField
     * @return mixed
     */
    public function find($indexField=null) {
        $query = new SelectQuery($this->getTable());
        $query->limit($this->getLimit());
        $query->offset($this->getOffset());
        $query->distinct($this->getDistinct());
        $query->selectFields($this->getSelectFields());
        $query->orderByFields($this->getOrderByFields());
        $query->groupByFields($this->getGroupByFields());
        $query->whereConditionGroup($this->getWhereConditionGroup());
        $query->havingConditionGroup($this->getHavingConditionGroup());
        $query->joins($this->getJoins());
        $results = $this->connection->query($query);
        if (!empty($results) && $indexField != null) {
            $returnIndexField = $indexField;
            if (($pos = strpos($returnIndexField, '.')) !== false) {
                $returnIndexField = substr($returnIndexField, $pos + 1);
            }
            $indexedResults = [];
            foreach ($results as $result) {
                $indexedResults[$result->$returnIndexField] = $result;
            }
            $results = $indexedResults;
        }
        return $results;
    }

    /**
     * Inserta un nuevo registro
     * @param array $fields
     * @return mixed
     */
    public function insert(array $fields = []) {
        $query = new InsertQuery($this->getTable());
        $query->fields(!empty($fields)? $fields : $this->getFields());
        return $this->connection->exec($query);
    }

    /**
     * Actualiza un registro
     * @param array $fields
     * @return mixed
     */
    public function update(array $fields = []) {
        $query = new UpdateQuery($this->getTable());
        $query->fields(!empty($fields)? $fields : $this->getFields());
        $query->whereConditionGroup($this->getWhereConditionGroup());
        return $this->connection->exec($query);
    }

    /**
     * Borra un registro
     * @return mixed
     */
    public function delete() {
        $query = new DeleteQuery($this->getTable());
        $query->whereConditionGroup($this->getWhereConditionGroup());
        return $this->connection->exec($query);
    }
}