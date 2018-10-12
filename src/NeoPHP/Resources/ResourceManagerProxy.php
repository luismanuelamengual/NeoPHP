<?php

namespace NeoPHP\Resources;

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

class ResourceManagerProxy {

    use TableTrait,
        FieldsTrait,
        SelectModifiersTrait,
        SelectFieldsTrait,
        OrderByFieldsTrait,
        GroupByFieldsTrait,
        WhereConditionsTrait,
        HavingConditionsTrait,
        JoinsTrait;

    private $resourceManager;

    /**
     * ResourceManagerProxy constructor.
     * @param ResourceManager $resourceManager
     */
    public function __construct(ResourceManager $resourceManager, $resourceName) {
        $this->resourceManager = $resourceManager;
        $this->source($resourceName);
    }

    /**
     * @return SelectQuery
     */
    protected function createSelectQuery(): SelectQuery {
        $query = new SelectQuery($this->getTable());
        $query->limit($this->getLimit());
        $query->offset($this->getOffset());
        $query->distinct($this->getDistinct());
        $query->selectFields($this->getSelectFields());
        $query->orderByFields($this->getOrderByFields());
        $query->groupByFields($this->getGroupByFields());
        $query->whereConditionGroup(clone $this->getwhereConditionGroup());
        $query->havingConditionGroup(clone $this->getHavingConditionGroup());
        $query->joins($this->getJoins());
        return $query;
    }

    /**
     * @param array $fields
     * @return InsertQuery
     */
    protected function createInsertQuery(array $fields = []): InsertQuery {
        $query = new InsertQuery($this->getTable());
        $query->fields(!empty($fields)? $fields : $this->getFields());
        return $query;
    }

    /**
     * @param array $fields
     * @return UpdateQuery
     */
    protected function createUpdateQuery(array $fields = []): UpdateQuery {
        $query = new UpdateQuery($this->getTable());
        $query->fields(!empty($fields)? $fields : $this->getFields());
        $query->whereConditionGroup($this->getWhereConditionGroup());
        return $query;
    }

    /**
     * @return DeleteQuery
     */
    protected function createDeleteQuery(): DeleteQuery {
        $query = new DeleteQuery($this->getTable());
        $query->whereConditionGroup($this->getWhereConditionGroup());
        return $query;
    }

    /**
     * @param $field
     * @param $indexField
     * @return array
     */
    public function pluck($field, $indexField=null) {

        $usingFormatting = preg_match_all('/{(\w+)}/', $field, $matches);

        //Obtención del campo/s de la consulta
        $fields = [];
        if ($usingFormatting) {
            $fields = $matches[1];
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

        //Creación del array de resultados
        $fieldResults = [];
        $results = $this->find();
        foreach ($results as $result) {
            $value = null;
            if (!$usingFormatting) {
                $value = $result->{$fields[0]};
            }
            else {
                $value = $field;
                foreach ($fields as $returnField) {
                    $value = str_replace("{" . $returnField . "}", $result->$returnField, $value);
                }
            }

            if ($indexField != null) {
                $fieldResults[$result->$indexField] = $value;
            }
            else {
                $fieldResults[] = $value;
            }
        }
        return $fieldResults;
    }

    /**
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
     * de manera gradual (por chunks)
     * @param int $size cantidad de resultados por chunk
     * @param callable $clousure funcion a ser llamada con cada chunk
     * @return $this referencia a la recurso
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
            $continueChunking = $clousure($chunkResults);
            if (isset($continueChunking) && $continueChunking === false) {
                break;
            }
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
     * @param $indexField
     * @return mixed
     */
    public function find($indexField=null) {

        //Obtención de resultados de búsqueda
        $results = $this->resourceManager->find($this->createSelectQuery());

        //Indexado de resultados
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
     * @return mixed
     */
    public function insert(array $fields = []) {
        return $this->resourceManager->insert($this->createInsertQuery($fields));
    }

    /**
     * @return mixed
     */
    public function update(array $fields = []) {
        return $this->resourceManager->update($this->createUpdateQuery($fields));
    }

    /**
     * @return mixed
     */
    public function delete() {
        return $this->resourceManager->delete($this->createDeleteQuery());
    }
}