<?php

namespace NeoPHP\Resources;

use RuntimeException;
use NeoPHP\Database\Query\ConditionGroup;

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
     * Busca recursos en el sistema
     * @param $resourceName
     * @return mixed
     */
    public function findResources ($resourceName) {
        $request = get_request();
        $resource = Resources::get($resourceName);
        if ($request->has("rawQuery")) {
            $query = unserialize($request->get("rawQuery"));
            $resource->limit($query->getLimit());
            $resource->offset($query->getOffset());
            $resource->distinct($query->getDistinct());
            $resource->selectFields($query->getSelectFields());
            $resource->orderByFields($query->getOrderByFields());
            $resource->groupByFields($query->getGroupByFields());
            $resource->whereConditionGroup($query->getwhereConditionGroup());
            $resource->havingConditionGroup($query->getHavingConditionGroup());
            $resource->joins($query->getJoins());
        }
        else {
            if ($request->has("limit")) {
                $resource->limit($request->get("limit"));
            }
            else {
                $resource->limit(100);
            }
            if ($request->has("offset")) {
                $resource->offset($request->get("offset"));
            }
            if ($request->has("distinct")) {
                $resource->distinct($request->get("distinct"));
            }
            if ($request->has("select")) {
                $resource->selectFields(explode(",", $request->get("select")));
            }
            if ($request->has("orderBy")) {
                $resource->orderByFields(explode(",", $request->get("orderBy")));
            }
            if ($request->has("groupBy")) {
                $resource->groupByFields(explode(",", $request->get("groupBy")));
            }
            if ($request->has("where")) {
                $resource->whereConditionGroup($this->createConditionGroup($request->get("where")));
            }
            if ($request->has("having")) {
                $resource->havingConditionGroup($this->createConditionGroup($request->get("having")));
            }
            if ($request->has("query")) {
                $resource->where("query", $request->get("query"));
            }
            if ($request->has("id")) {
                $resource->where("id", $request->get("id"));
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
        $resource = Resources::get($resourceName);
        if ($request->has("rawQuery")) {
            $query = unserialize($request->get("rawQuery"));
            $resource->fields($query->getFields());
        }
        return $resource->insert();
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