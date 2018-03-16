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
        $this->table($resourceName);
    }

    /**
     * @return SelectQuery
     */
    protected function createSelectQuery(): SelectQuery {
        $query = new SelectQuery($this->table());
        $query->limit($this->limit());
        $query->offset($this->offset());
        $query->distinct($this->distinct());
        $query->selectFields($this->selectFields());
        $query->orderByFields($this->orderByFields());
        $query->groupByFields($this->groupByFields());
        $query->whereConditions($this->whereConditions());
        $query->havingConditions($this->havingConditions());
        $query->joins($this->joins());
        return $query;
    }

    /**
     * @param array $fields
     * @return InsertQuery
     */
    protected function createInsertQuery(array $fields = []): InsertQuery {
        $query = new InsertQuery($this->table());
        $query->fields(!empty($fields)? $fields : $this->fields());
        return $query;
    }

    /**
     * @param array $fields
     * @return UpdateQuery
     */
    protected function createUpdateQuery(array $fields = []): UpdateQuery {
        $query = new UpdateQuery($this->table());
        $query->fields(!empty($fields)? $fields : $this->fields());
        $query->whereConditions($this->whereConditions());
        return $query;
    }

    /**
     * @return DeleteQuery
     */
    protected function createDeleteQuery(): DeleteQuery {
        $query = new DeleteQuery($this->table());
        $query->whereConditions($this->whereConditions());
        return $query;
    }

    /**
     * @return mixed
     */
    public function find() {
        return $this->resourceManager->find($this->createSelectQuery());
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