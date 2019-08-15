<?php

namespace NeoPHP\Resources;

use NeoPHP\Query\DeleteQuery;
use NeoPHP\Query\InsertQuery;
use NeoPHP\Query\Query;
use NeoPHP\Query\QueryParser;
use NeoPHP\Query\SelectQuery;
use NeoPHP\Query\UpdateQuery;
use RuntimeException;
use NeoPHP\Query\ConditionGroup;

class ResourceController {

    const CONDITION_ELEMENT_START = "[";
    const CONDITION_ELEMENT_END = "]";
    const CONDITION_ELEMENT_ARGUMENT_SEPARATOR = ",";
    const CONDITION_TYPE_AND = "AND";
    const CONDITION_TYPE_OR = "OR";
    const CONDITION_TYPE_ON = "ON";
    const CONDITION_TYPE_FIELD = "FIELD";
    const CONDITION_TYPE_NULL = "NULL";
    const CONDITION_TYPE_NOTNULL = "NOTNULL";

    /**
     * Obtiene el manager del recurso
     * @param $resourceName
     * @return ResourceManagerProxy
     */
    private function getResourceManager ($resourceName) : ResourceManagerProxy {
        $resource = Resource::get($resourceName);
        $resourceManager = $resource->getManager();
        if ($resourceManager instanceof DefaultResourceManager) {
            throw new RuntimeException("Default database resources are forbidden remotely");
        }
        return $resource;
    }

    /**
     * Ejecuta una consulta de recursos
     */
    public function queryResources () {
        $sql = get_request()->content();
        $contentType = get_request()->header("Content-Type");
        if ("application/sql" == $contentType) {
            $query = unserialize($sql);
        }
        elseif (!($sql instanceof Query)) {
            $query = QueryParser::parseQuery($sql);
        }
        $result = null;
        $resource = $this->getResourceManager($query->getSource());
        if ($query instanceof SelectQuery) {
            $resource->selectFields($query->getSelectFields());
            $resource->limit($query->getLimit());
            $resource->offset($query->getOffset());
            $resource->distinct($query->getDistinct());
            $resource->orderByFields($query->getOrderByFields());
            $resource->groupByFields($query->getGroupByFields());
            $resource->whereConditionGroup($query->getWhereConditionGroup());
            $resource->havingConditionGroup($query->getHavingConditionGroup());
            $resource->joins($query->getJoins());
            $result = $resource->find();
        }
        if ($query instanceof InsertQuery) {
            $resource->fields($query->getFields());
            $result = $resource->insert();
        }
        if ($query instanceof UpdateQuery) {
            $resource->fields($query->getFields());
            $resource->whereConditionGroup($query->getwhereConditionGroup());
            $result = $resource->update();
        }
        if ($query instanceof DeleteQuery) {
            $resource->whereConditionGroup($query->getwhereConditionGroup());
            $result = $resource->delete();
        }
        return $result;
    }

    /**
     * Busca recursos en el sistema
     * @param $resourceName
     * @return mixed
     */
    public function findResources ($resourceName) {
        $request = get_request();
        $resource = $this->getResourceManager($resourceName);
        $resource->limit(100);
        $parameters = $request->params();
        $sessionName = get_session()->name();
        foreach ($parameters as $key=>$value) {
            switch ($key) {
                case $sessionName:
                    break;
                case "limit":
                    $resource->limit($value);
                    break;
                case "offset":
                case "start":
                    $resource->offset($value);
                    break;
                case "distinct":
                    $resource->distinct($value);
                    break;
                case "select":
                    $resource->selectFields(explode(",", $value));
                    break;
                case "orderBy":
                    call_user_func_array([$resource, "orderBy"], explode(",", $value));
                    break;
                case "groupBy":
                    $resource->groupByFields(explode(",", $value));
                    break;
                case "where":
                    $resource->whereConditionGroup($this->createConditionGroup($value));
                    break;
                case "having":
                    $resource->havingConditionGroup($this->createConditionGroup($value));
                    break;
                case "filters":
                    $filters = $value;
                    foreach ($filters as $filter) {
                        if (!empty($filter["operator"])) {
                            $resource->where($filter["property"], $filter["operator"], $filter["property"]);
                        } else {
                            $resource->where($filter["property"], $filter["value"]);
                        }
                    }
                    break;
                default:
                    $resource->where($key, $value);
                    break;
            }
        }
        return $resource->find();
    }

    /**
     * Inserta un nuevo recurso
     * @param string $resourceName nombre del recurso
     * @return mixed resultado de la inserción del recurso
     */
    public function insertResource ($resourceName) {
        $request = get_request();
        $resource = $this->getResourceManager($resourceName);
        $parameters = $request->params();
        foreach ($parameters as $key=>$value) {
            $resource->set($key, $value);
        }
        return $resource->insert();
    }

    /**
     * @param string $resourceName nombre del recurso
     * @return mixed resultado de la inserción del recurso
     */
    public function updateResource ($resourceName) {
        throw new RuntimeException("Method not implemented !!", Response::HTTP_NOT_IMPLEMENTED);
    }

    /**
     * @param $resourceName
     */
    public function deleteResource ($resourceName) {
        throw new RuntimeException("Method not implemented !!", Response::HTTP_NOT_IMPLEMENTED);
    }

    /**
     * Comprueba que el recurso que se llama se tenga accesibilidad
     * @param string $resourceName Nombre de recurso
     */
    public function checkResourceAccsesibility ($resourceName) {
        $availiableResources = get_property("resources.managers");
        if (!array_key_exists($resourceName, $availiableResources)) {
            throw new RuntimeException("Resource \"$resourceName\" is not accesible !!");
        }
    }

    /**
     * Crea un grupo de condiciones a traves de un string
     * @param $conditionString
     * @return ConditionGroup
     */
    public function createConditionGroup($conditionString): ConditionGroup {
        $mainConditionGroup = null;
        $conditionGroupStack = [];
        $currentBasicConditionElement = null;

        $pos = 0;
        while ($pos < strlen($conditionString)) {
            if ($conditionString[$pos] == self::CONDITION_ELEMENT_ARGUMENT_SEPARATOR) {
                if (empty($conditionGroupStack) && $mainConditionGroup != null && sizeof($mainConditionGroup->conditions()) > 1) {
                    $childConditionGroup = $mainConditionGroup;
                    $mainConditionGroup = new ConditionGroup();
                    $mainConditionGroup->onGroup($childConditionGroup);
                }
                $pos++;
                continue;
            }

            $nextEndPos = strpos($conditionString, self::CONDITION_ELEMENT_END, $pos);
            $nextStartPos = strpos($conditionString, self::CONDITION_ELEMENT_START, $pos);
            if ($nextStartPos !== false) {
                if ($nextEndPos === false || $nextStartPos < $nextEndPos) {
                    $type = substr($conditionString, $pos, $nextStartPos - $pos);
                    if (empty($type)) {
                        $type = self::CONDITION_TYPE_ON;
                    }

                    switch ($type) {
                        case self::CONDITION_TYPE_AND:
                        case self::CONDITION_TYPE_OR:
                            //Se esta comenzando una condicion de tipo grupo
                            $currentConditionGroup = new ConditionGroup();
                            $currentConditionGroup->connector(($type == self::CONDITION_TYPE_AND) ? ConditionGroup::CONNECTOR_AND : ConditionGroup::CONNECTOR_OR);
                            array_push($conditionGroupStack, $currentConditionGroup);
                            break;
                        default:
                            //Se esta comenzando una condicion básica
                            $currentBasicConditionElement = [];
                            $currentBasicConditionElement["type"] = $type;
                            $currentBasicConditionElement["start"] = $nextStartPos;
                            break;
                    }
                    $pos = $nextStartPos + 1;
                }
            }
            if ($nextEndPos !== false) {
                if ($nextStartPos === false || $nextEndPos < $nextStartPos) {
                    if (!empty($currentBasicConditionElement)) {
                        //Se esta finalizando una condición básica
                        $type = $currentBasicConditionElement["type"];
                        $startPos = $currentBasicConditionElement["start"];
                        $endPos = $nextEndPos;

                        $lastConditionGroup = end($conditionGroupStack);
                        if ($lastConditionGroup === false) {
                            if ($mainConditionGroup == null) {
                                $mainConditionGroup = new ConditionGroup();
                            }
                            $lastConditionGroup = $mainConditionGroup;
                        }

                        $arguments = explode(self::CONDITION_ELEMENT_ARGUMENT_SEPARATOR, substr($conditionString, $startPos+1, ($endPos - $startPos)-1));

                        switch ($type) {
                            case self::CONDITION_TYPE_ON:
                                call_user_func_array([$lastConditionGroup, "on"], $arguments);
                                break;
                            case self::CONDITION_TYPE_FIELD:
                                call_user_func_array([$lastConditionGroup, "onField"], $arguments);
                                break;
                            case self::CONDITION_TYPE_NULL:
                                call_user_func_array([$lastConditionGroup, "onNull"], $arguments);
                                break;
                            case self::CONDITION_TYPE_NOTNULL:
                                call_user_func_array([$lastConditionGroup, "onNotNull"], $arguments);
                                break;
                            default:
                                throw new RuntimeException("Unrecognized condition type \"$type\" !!");
                        }

                        $currentBasicConditionElement = null;
                    }
                    else {
                        //Se esta finalizando una condición de tipo grupo
                        $lastConditionGroup = array_pop($conditionGroupStack);
                        if ($lastConditionGroup === false) {
                            throw new RuntimeException("Syntax error while closing condition element");
                        }

                        if ($mainConditionGroup == null) {
                            $mainConditionGroup = $lastConditionGroup;
                        }
                        else if ($lastConditionGroup != $mainConditionGroup) {
                            $parentConditionGroup = end($conditionGroupStack);
                            if ($parentConditionGroup === false) {
                                if ($mainConditionGroup == null) {
                                    $mainConditionGroup = new ConditionGroup();
                                }
                                $parentConditionGroup = $mainConditionGroup;
                            }

                            $parentConditionGroup->onGroup($lastConditionGroup);
                        }
                    }
                    $pos = $nextEndPos + 1;
                }
            }
            else {
                $pos = strlen($conditionString);
            }
        }

        return $mainConditionGroup;
    }
}