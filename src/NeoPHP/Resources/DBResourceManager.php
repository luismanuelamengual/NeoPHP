<?php

namespace NeoPHP\Resources;

use RuntimeException;
use NeoPHP\Database\DB;
use NeoPHP\Database\Query\ConditionGroup;
use NeoPHP\Database\Query\ConditionOperator;
use NeoPHP\Database\Query\ConditionType;
use NeoPHP\Database\Query\DeleteQuery;
use NeoPHP\Database\Query\InsertQuery;
use NeoPHP\Database\Query\Query;
use NeoPHP\Database\Query\SelectQuery;
use NeoPHP\Database\Query\UpdateQuery;
use stdClass;

/**
 * Class DBResourceManager
 * @package NeoPHP\Resources
 */
abstract class DBResourceManager extends ResourceManager {

    /**
     * Variable para nombre de coneccion a la DB
     * @var null
     */
    protected $dbConnection = null;

    /**
     * Retirba el nombre de la conecction a usar
     * @return Connection connection
     */
    protected function getConnection() : Connection {
        return DB::connection($this->dbConnection);
    }

    /**
     * Retorna el nombre de la tabla que corresponde al recurso
     * @return null
     */
    protected function getTableName() {
        return null;
    }

    /**
     * Retorna un mapa con los nombres de las columnas que corresponden a los campos del recurso. Si el nombre de la columna
     * es igual al nombre del campo entonces se puede indicar sin key.
     * @return null
     */
    protected function getColumnNames() {
        return null;
    }

    /**
     * Indica si debe considerar case en los mapeos de nombres de campos (prepareSelectQuery)
     * Por defecto es false, pero REQUIERE que el mapa de columnNames este en MINUSCULAS. De esta manera, si se indica
     * un filtro o select u otra cosa buscara en el mapa de columnNames sin importarle el case.
     * @return bool
     */
    protected function isFieldCaseSensitive() : bool {
        return false;
    }

    /**
     * Retorna la lista con los nombres de los campos requeridos como condiciones de la consulta
     * @return null
     */
    protected function getRequiredFields() {
        return null;
    }

    /**
     * Obtiene el límite por defecto de la consulta
     * @return int
     */
    protected function getDefaultLimit() {
        return 1000;
    }

    /**
     * Obtiene el límite maximo de la consulta
     * @return int
     */
    protected function getMaxLimit() {
        return 20000;
    }

    /**
     * Renombra campos, verifica limit y ejecuta el select en DB
     * @param SelectQuery $query
     * @return array resultados
     */
    public function find(SelectQuery $query) {
        $this->verifySelectQuery($query);
        return $this->executeSelectQuery($query);
    }

    /**
     * Renombra set y conditions y ejecuta el update en DB
     * @param UpdateQuery $query
     * @return mixed
     */
    public function update(UpdateQuery $query) {
        return $this->executeUpdateQuery($query);
    }

    /**
     * Renombra sets y ejecuta insert en DB
     * @param InsertQuery $query
     * @return mixed
     */
    public function insert(InsertQuery $query) {
        return $this->executeInsertQuery($query);
    }

    /**
     * Renombra condiciones y ejecuta delete en DB
     * @param DeleteQuery $query
     * @return bool|int
     */
    public function delete(DeleteQuery $query) {
        return $this->executeDeleteQuery($query);
    }

    /**
     * Ejecuta una consulta de recurso
     * @param SelectQuery $query
     * @return mixed
     */
    protected function executeSelectQuery(SelectQuery $query) {
        $this->prepareSelectQuery($query);
        return $this->getConnection()->query($query);
    }

    /**
     * Ejecuta una consulta de actualización de recurso
     * @param UpdateQuery $query
     * @return bool|int
     */
    protected function executeUpdateQuery(UpdateQuery $query) {
        $this->prepareUpdateQuery($query);
        return $this->getConnection()->exec($query);
    }

    /**
     * Ejecuta una consulta de creación de recurso
     * @param InsertQuery $query
     * @return bool|int
     */
    protected function executeInsertQuery(InsertQuery $query) {
        $this->prepareInsertQuery($query);
        return $this->getConnection()->exec($query);
    }

    /**
     * Ejecuta una consulta de borrado de recurso
     * @param DeleteQuery $query
     * @return bool|int
     */
    protected function executeDeleteQuery(DeleteQuery $query) {
        $this->prepareDeleteQuery($query);
        return $this->getConnection()->exec($query);
    }

    /**
     * Verifica la validez del query a ejecutar
     * @param SelectQuery $query consulta a ejecutar
     */
    protected function verifySelectQuery(SelectQuery $query) {
        $this->verifyScope($query);
        $this->verifyRequiredFields($query);
        $this->verifyFields($query);
        $this->verifyLimits($query);
    }

    /**
     * Verificaciones de validez del query
     * @param SelectQuery $query Consulta a ejecutar
     */
    protected function verifyScope (SelectQuery $query) {
        if (!empty($query->getJoins())) {
            throw new RuntimeException('Joins are not allowed on this resource');
        }
    }

    /**
     * Valida que el query contenga los campos requeridos
     * @param SelectQuery $query
     * @param array|null $requiredFields
     */
    protected function verifyRequiredFields(SelectQuery $query, array $requiredFields = null) {
        if ($requiredFields == null) {
            $requiredFields = $this->getRequiredFields();
        }
        if (!empty($requiredFields)) {
            foreach ($requiredFields as $requiredField) {
                if (empty($query->getWhereCondition($requiredField, null, false, true))) {
                    throw new RuntimeException("Condition required: $requiredField");
                }
            }
        }
    }

    /**
     * Valida que el query no invoque campos no definidos en el recurso y no tenga joins
     * @param SelectQuery $query
     */
    protected function verifyFields(SelectQuery $query) {
        $foundField = null;
        if ($this->hasOtherFields($query, null, false, $foundField)) {
            throw new RuntimeException("Invalid fields on query : $foundField");
        }
    }

    /**
     * Verifica que se hayan establecido los limites de la consulta
     * @param SelectQuery $query
     */
    protected function verifyLimits (SelectQuery $query) {
        if (empty($query->getLimit())) {
            $query->limit($this->getDefaultLimit());

        } else if ($query->getLimit() > $this->getMaxLimit()) {
            $query->limit($this->getMaxLimit());
        }
    }

    /**
     * Renombra las distintas partes del query:
     * condiciones, order, group, joins y agrega select as para que el resultado ya este con los nuevos nombres
     * @param SelectQuery $query
     */
    protected function prepareSelectQuery(SelectQuery $query) {
        $this->renameQuery($query);
    }

    /**
     * Renombra las distintas partes del update: conditions y fields
     * @param UpdateQuery $query
     */
    private function prepareUpdateQuery(UpdateQuery $query) {
        $this->renameQuery($query);
    }

    /**
     * Renombra el source y set fields del insert
     * @param InsertQuery $query
     */
    private function prepareInsertQuery(InsertQuery $query) {
        $this->renameQuery($query);
    }

    /**
     * Renombre source y conditions del delete
     * @param DeleteQuery $query
     */
    private function prepareDeleteQuery(DeleteQuery $query) {
        $this->renameQuery($query);
    }

    /**
     * Indica si hay mapeos de nombres en columnNames
     * Es decir, valido que sea un mapa y no una lista. Puede ser una lista para el caso donde no hay cambio de nombre
     * pero se requiere cargar la lista de fields para validar que no accedan a otros fields o bien para reponder al select *
     *
     * @param $columnNames
     * @return bool
     */
    private function hasFieldMappings($columnNames = null) {
        $result = false;
        if($columnNames == null) {
            $columnNames = $this->getColumnNames();
        }
        if (!empty($columnNames)) {
            foreach ($columnNames  as $aliasName => $columnName) {
                if(!is_numeric($aliasName)) {
                    $result = true;
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * Renombra una consulta (nombre de tabla y campos)
     * @param Query $query query a renombrar
     * @param null|string $tableName nombre de la tabla
     * @param null|array $columnNames columnas de la tabla
     */
    protected function renameQuery (Query $query, $tableName = null, $columnNames = null) {
        $originalSource = $this->renameSource($query, $tableName);

        if($columnNames == null) {
            $columnNames = $this->getColumnNames();
        }

        if ($query instanceof SelectQuery) {
            $hasFieldMappings = $this->hasFieldMappings($columnNames);
            if (!empty($columnNames)) {
                //si $hasFieldMappings == false llamo igual para el caso de select * que complete solo son lo indicado en columnNames
                $this->renameSelectFields($query, $columnNames, $originalSource);

                if ($hasFieldMappings) {
                    if ($query->hasWhereConditions()) {
                        $this->renameConditionFields($query->getWhereConditionGroup(), $columnNames, $originalSource, $query->getTable());
                    }

                    $this->renameOrderByFields($query, $columnNames, $originalSource);
                    $this->renameGroupByFields($query, $columnNames, $originalSource);
                    $this->renameJoinFields($query, $columnNames, $originalSource);
                }
            }
        }
        else if ($query instanceof UpdateQuery) {
            $hasFieldMappings = $this->hasFieldMappings($columnNames);
            if ($hasFieldMappings) {
                if ($query->hasWhereConditions()) {
                    $this->renameConditionFields($query->getWhereConditionGroup(), $columnNames, $originalSource, $query->getTable());
                }
                $this->renameSetFields($query, $columnNames, $originalSource, $query->getTable());
            }
        }
        else if ($query instanceof InsertQuery) {
            $hasFieldMappings = $this->hasFieldMappings($columnNames);
            if ($hasFieldMappings) {
                $this->renameSetFields($query, $columnNames, $originalSource, $query->getTable());
            }
        }
        else if ($query instanceof DeleteQuery) {
            $hasFieldMappings = $this->hasFieldMappings($columnNames);
            if ($hasFieldMappings) {
                if ($query->hasWhereConditions()) {
                    $this->renameConditionFields($query->getWhereConditionGroup(), $columnNames, $originalSource, $query->getTable());
                }
            }
        }
    }

    /**
     * Renombra la fuente de datos por el nombre de la tabla indicada
     * @param Query $query
     * @param string $tableName
     * @return string
     */
    private function renameSource(Query $query, string $tableName = null):string {
        if($tableName == null) {
            $tableName = $this->getTableName();
        }
        $previusSource = $query->getTable();
        if(!empty($tableName)) {
            $query->table($tableName);
        }
        return $previusSource;
    }

    /**
     * Renombra los campos involucrados en condiciones where segun el mapa indicado
     * @param ConditionGroup $conditionGroup
     * @param $namesMap
     * @param null $originalSource
     * @param null $newSource
     */
    private function renameConditionFields(ConditionGroup $conditionGroup, $namesMap = null, $originalSource = null, $newSource = null) {
        if (!$conditionGroup->isEmpty()) {
            if($namesMap == null) {
                $namesMap = $this->getColumnNames();
            }
            $conditions = &$conditionGroup->getConditions();
            foreach ($conditions as $key=>&$condition) {
                switch ($condition->type) {
                    case ConditionType::GROUP:
                        $this->renameConditionFields($condition->group, $namesMap, $originalSource, $newSource);
                        break;
                    case ConditionType::BASIC:
                        $newName = $this->getFieldNewName($condition->field, $namesMap, $originalSource, $newSource);
                        if($newName != null) {
                            $condition->field = $newName;
                        }
                        if ($condition->operator == ConditionOperator::EQUALS_FIELD) {
                            $newName = $this->getFieldNewName($condition->value, $namesMap, $originalSource, $newSource);
                            if($newName != null) {
                                $condition->value = $newName;
                            }
                        }
                        break;
                    case ConditionType::RAW:
                        foreach ($namesMap as $fieldName => $dbName) {
                            $condition->sql = preg_replace('/\b' . $fieldName . '(?=$|\s)/', $dbName, $condition->sql);
                            $condition->sql = preg_replace('/\b' . $originalSource.$fieldName . '(?=$|\s)/', "$newSource.$dbName", $condition->sql);
                        }
                        break;
                }
            }
        }
    }

    /**
     * Renombra los campos set de la sentencia update
     * @param Query $query
     * @param $columnNames
     * @param $originalSource
     * @param $newSource
     */
    private function renameSetFields(Query $query, $columnNames, $originalSource, $newSource) {
        $renamedFields = [];
        foreach ($query->getFields() as $field => $value) {
            $newName = $this->getFieldNewName($field, $columnNames, $originalSource, $newSource);
            if($newName != null) {
                $renamedFields[$newName] = $value;
            }
        }
        $query->fields($renamedFields);
    }

    /**
     * Indica si el field indicado se encuentra en la lista indicada como segundo argumento,
     * considerando que se indique caseSensitive en false
     * @param $field
     * @param $list
     * @return bool true si field es encontrado en list, false en caso contrario
     */
    protected function fieldInList($field, array $list) : bool {
        $result = in_array($field, $list);
        if (!$result && !$this->isFieldCaseSensitive()) {
            $result = in_array(strtolower($field), $list);
        }
        return $result;
    }

    /**
     * Retorna el valor correspondiente al field indicado segun el map indicado como segundo argumento,
     * considerando que se indique caseSensitive en false
     * @param $field
     * @param array $map
     * @return mixed null si no hay entrada en el mapa para el field, el valor correspondiente si lo hay.
     */
    private function fieldInMap($field, array $map) {
        $result = null;
        if (array_key_exists($field, $map)) {
            $result = $map[$field];

        } else if (!$this->isFieldCaseSensitive()) {
            $field = strtolower($field);
            //$map = array_map('strtolower', $map);
            if (array_key_exists($field, $map)) {
                $result = $map[$field];
            }
        }
        return $result;
    }

    /**
     * Busca el posible remplazo de nombre
     * @param $field
     * @param $namesMap
     * @param mixed $originalSource
     * @param mixed $newSource
     * @return mixed|string
     */
    private function getFieldNewName($field, $namesMap = null, $originalSource = null, $newSource = null) {
        $newName = null;
        if ($namesMap == null) {
            $namesMap = $this->getColumnNames();
        }
        //primero busca una entrada en el mapa para el nombre del campo indicado
        $newName = $this->fieldInMap($field, $namesMap);
        if (empty($newName) && $this->fieldInList($field, $namesMap)) {
            $newName = $field;
        }
        if (empty($newName)) {
            //si el field indicado comienza con nombre de tabla seguido de punto
            $fieldPos = strpos($field, "$originalSource.");
            if($fieldPos === 0) {
                $searchField = substr($field, strlen($originalSource)+1);
                $newName = $this->fieldInMap($searchField, $namesMap);
                if(!empty($newName) && !empty($newSource) && strpos($newName, '.') === false) {
                    $newName = "$newSource.$newName";
                }
            }
        }
        if (empty($newName)) {
            if (preg_match ( "/\w+\s*\(\s*(\w+)\s*\)/", $field, $matches)) {
                $newName = str_replace($matches[1], $this->getFieldNewName($matches[1]), $matches[0]);
            }
        }

        return empty($newName) ? null : $newName;
    }

    /**
     * Modifica el select con los alias correspondientes
     *
     * Nota: si la tabla tiene algunos campos a cambiar y otros no, en el mapa debemos poner todos para el caso de select *.
     * Es por ello que sumo una validacion para que en el caso que el mapa contenga una entrada donde clave y valor
     * son iguales no ponga el alias, o bien que tenga solo valor, en ese caso la clave es un autonumerico y por eso
     * pregunto por is_numeric
     * @param SelectQuery $query
     * @param $namesMap
     * @param mixed $originalSource
     */
    private function renameSelectFields(SelectQuery $query, $namesMap = null, $originalSource = null) {
        if($namesMap == null) {
            $namesMap = $this->getColumnNames();
        }
        $selectFields = &$query->getSelectFields();
        //en el caso que no se indique nada en el select, pongo los del mapa
        if (empty($selectFields)) {
            foreach ($namesMap as $aliasName => $columnName) {
                if (is_numeric($aliasName)) {
                    $selectItem = $columnName;
                } else if (empty($columnName) || $aliasName === $columnName) {
                    $selectItem = $aliasName;
                } else {
                    $selectItem = [$columnName, $aliasName];
                }
                $query->select($selectItem);
            }
        } else {
            //primer for para busqueda de * o tabla.* y agregado de campos
            $newSelectFields = [];
            foreach ($selectFields as $selectField) {
                $aliasName = $selectField instanceof stdClass ? trim($selectField->expression) : trim($selectField);
                if ($aliasName == '*') {
                    foreach ($namesMap as $aliasName => $columnName) {
                        if (is_numeric($aliasName)) {
                            $newSelectFields[$columnName] = $columnName;
                        } else {
                            $newSelectFields[$aliasName] = $aliasName;
                        }
                    }
                    continue;

                } else if (!empty($originalSource) && $aliasName == "$originalSource.*") {
                    foreach ($namesMap as $aliasName => $columnName) {
                        if (is_numeric($aliasName)) {
                            $newSelectFields["$originalSource.$columnName"] = "$originalSource.$columnName";
                        } else {
                            $newSelectFields["$originalSource.$aliasName"] = "$originalSource.$aliasName";
                        }
                    }
                    continue;

                } else {
                    $newSelectFields[$aliasName] = $selectField;
                }
            }
            if (!empty($newSelectFields)) {
                $query->selectFields(array_values($newSelectFields));
            }
            //segundo for para reemplazo de nombres
            foreach ($selectFields as &$field) {
                if ($field instanceof stdClass) {
                    $aliasName = $field->alias;
                    $columnName = $this->getFieldNewName($field->expression, $namesMap, $originalSource, $query->getTable());
                } else {
                    $aliasName = $field;
                    $columnName = $this->getFieldNewName($field, $namesMap, $originalSource, $query->getTable());
                }
                if($columnName != null) {
                    if ($columnName == $aliasName) {
                        $field = $columnName;
                    } else {
                        $field = new stdClass();
                        $field->expression = $columnName;
                        $field->alias = $aliasName;
                    }
                }
            }
        }
    }

    /**
     * Renombra los campos involucrados en ordenamiento segun el mapa indicado
     * @param SelectQuery $query
     * @param $namesMap
     * @param mixed $originalSource
     */
    private function renameOrderByFields(SelectQuery $query, $namesMap = null, $originalSource = null) {
        if($namesMap == null) {
            $namesMap = $this->getColumnNames();
        }
        $orderByFields = &$query->getOrderByFields();
        if (!empty($orderByFields)) {
            foreach ($orderByFields as &$orderByField) {
                $newName = $this->getFieldNewName($orderByField->field, $namesMap, $originalSource, $query->getTable());
                if($newName != null) {
                    $orderByField->field = $newName;
                }
            }
        }
    }

    /**
     * Renombra los campos involucrados en agrupamiento segun el mapa indicado
     * @param SelectQuery $query
     * @param $namesMap
     * @param mixed $originalSource
     */
    private function renameGroupByFields(SelectQuery $query, $namesMap = null, $originalSource = null) {
        if($namesMap == null) {
            $namesMap = $this->getColumnNames();
        }
        $groupByFields = &$query->getGroupByFields();
        if (!empty($groupByFields)) {
            $source = $query->getTable();
            $newGroupByFields = [];
            foreach ($groupByFields as $field) {
                if ($field == '*') {
                    foreach ($namesMap as $aliasName => $columnName) {
                        $newGroupByFields[$columnName] = 1;
                    }
                    continue;

                } else if (!empty($originalSource) && $field == "$originalSource.*") {

                    foreach ($namesMap as $aliasName => $columnName) {
                        $newGroupByFields["$source.$columnName"] = 1;
                    }
                    continue;

                } else {
                    $newName = $this->getFieldNewName($field, $namesMap, $originalSource, $source);
                    if($newName != null) {
                        $newGroupByFields[$newName] = 1;
                    }
                }
            }
            if (!empty($newGroupByFields)) {
                $query->groupByFields(array_keys($newGroupByFields));
            }
        }
    }

    /**
     * Renombra los campos involucrados en joins segun el mapa indicado
     * @param SelectQuery $query
     * @param $namesMap
     * @param mixed $originalSource
     */
    private function renameJoinFields(SelectQuery $query, $namesMap = null, $originalSource = null) {
        if($namesMap == null) {
            $namesMap = $this->getColumnNames();
        }
        $joins = &$query->getJoins();
        if(!empty($joins)) {
            foreach ($joins as &$join) {
                $this->renameConditionFields($join, $namesMap, $originalSource, $query->getTable());
            }
        }
    }

    /**
     * Retorna verdadero si en el query encuentra otro field distinto a los indicados, revisando:
     * -conditionFields
     * -selectFields
     * -orderByFields
     * -groupByFields (no tiene sentido porque exige que este en select)
     * -joinFields
     * -distinct?
     * -having?
     *
     * @param SelectQuery $query
     * @param array $fieldNames : si no se indica, entonces valida contra los columnNames definidos
     * @param bool $selectAllMeansOther : si es true y hay un select * retorna true = hasOtherFields
     * @param null $foundField : referencia donde se coloca el nombre del primer field encontrado distinto de los indicados
     * @return bool
     */
    protected function hasOtherFields(SelectQuery $query, $fieldNames = null, bool $selectAllMeansOther = false, &$foundField = null) : bool {
        $result = false;
        if ($fieldNames == null) {
            $fieldNames = $this->getFields($this->getColumnNames());
        }

        if (!empty($fieldNames)) {
            $source = $query->getTable();

            //select fields
            $result = $this->hasOtherSelectField($query, $fieldNames, $selectAllMeansOther, $foundField);

            //condition fields
            if (!$result && $query->hasWhereConditions()) {
                $result = $this->hasOtherConditionField($query->getWhereConditionGroup(), $source, $fieldNames, $foundField);

            }

            //order fields
            $orders = $query->getOrderByFields();
            if (!$result && !empty($orders)) {
                foreach ($orders as $order) {
                    $field = $order->field;
                    $tablePos = stripos($field, "$source.");
                    if ($tablePos === 0) {
                        $field = substr($field, strlen($source) + 1);
                    }
                    if (!$this->fieldInList($field, $fieldNames)) {
                        $result = true;
                        $foundField = $field;
                        break;
                    }
                }
            }

            //join fields
            $joins = $query->getJoins();
            if (!$result && !empty($joins)) {
                foreach ($joins as $join) {
                    $result = $this->hasOtherConditionField($join, $source, $fieldNames, $foundField);
                    if ($result) {
                        break;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Retorna una lista con los nombres de los campos definidos en el mapa provisto, segun tenga key-value o solo value.
     * Ej en ColumnNames retorna el nombre de los campos considerando que: si el key es numerico, entones indica que el
     * nombre de la columna en db es igual al nombre del campo, y en caso contrario el key es el nombre del campo y el
     * value es el nombre de la columna en base de datos.
     * @param $namesMap
     * @return mixed
     */
    protected function getFields($map) {
        $result = null;
        if (!empty($map)) {
            foreach ($map as $key => $value) {
                if (is_numeric($key)) {
                    $result[] = $value;
                } else {
                    $result[] = $key;
                }
            }
        }
        return $result;
    }

    /**
     * Retorna verdadero si en entre los fields solicitados (select) encuentra otro field distinto a los indicados
     * @param SelectQuery $query
     * @param array $fieldNames
     * @param bool $selectAllMeansOther
     * @param null $foundField : referencia donde se coloca el nombre del primer field encontrado distinto de los indicados
     * @return bool
     */
    protected function hasOtherSelectField(SelectQuery $query, array $fieldNames, bool $selectAllMeansOther = false, &$foundField = null) : bool {
        $result = false;
        $selectFields = &$query->getSelectFields();
        if (empty($selectFields)) {
            $result = $selectAllMeansOther;
            $foundField = '*';
        } else {
            $source = $query->getTable();
            foreach ($selectFields as $selectField) {
                $field = $selectField instanceof stdClass ? $selectField->expression : $selectField;

                if (trim($field) == '*' || $field == "$source.*") {
                    $result = $selectAllMeansOther;
                    $foundField = $field;
                    continue;
                }

                if (preg_match ( "/\w+\s*\(\s*(\w+)\s*\)/", $field, $matches)) {
                    if (sizeof($matches) >= 2) {
                        $field = trim($matches[1]);
                    }
                }

                $tablePos = stripos($field, "$source.");
                if ($tablePos === 0) {
                    $field = substr($field, strlen($source) + 1);
                }

                if (!$this->fieldInList($field, $fieldNames)) {
                    $result = true;
                    $foundField = $field;
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * Retorna verdadero si en entre las codiciones encuentra otro field distinto a los indicados
     * @param ConditionGroup $conditionGroup
     * @param $source
     * @param array $fieldNames
     * @param null $foundField : referencia donde se coloca el nombre del primer field encontrado distinto de los indicados
     * @return bool
     */
    private function hasOtherConditionField(ConditionGroup $conditionGroup, string $source, array $fieldNames, &$foundField = null) : bool {
        $result = false;
        $conditions = &$conditionGroup->getConditions();
        foreach ($conditions as $key=>&$condition) {
            if ($condition->type == ConditionType::GROUP) {
                $result = $this->hasOtherConditionField($condition->group, $source, $fieldNames, $foundField);
                if ($result) {
                    break;
                }
            } else if ($condition->type == ConditionType::RAW) {
                $result = true;
                $foundField = 'RAW';
                break;
            } else {
                $field = $condition->field;
                $tablePos = stripos($field, "$source.");
                if($tablePos === 0) {
                    $field = substr($field, strlen($source)+1);
                }
                if (!$this->fieldInList($field, $fieldNames)) {
                    $result = true;
                    $foundField = $field;
                    break;
                }

                if ($condition->operator == ConditionOperator::EQUALS_FIELD) {
                    $field = $condition->value;
                    $tablePos = stripos($field, "$source.");
                    if($tablePos === 0) {
                        $field = substr($field, strlen($source)+1);
                    }
                    if (!$this->fieldInList($field, $fieldNames)) {
                        $result = true;
                        $foundField = $field;
                        break;
                    }
                }
            }
        }
        return $result;
    }
}