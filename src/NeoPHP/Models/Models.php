<?php

namespace NeoPHP\Models;

/**
 * Class Models
 * @package NeoPHP\Models
 */
abstract class Models {

    private static $managers = [];

    /**
     * @param $objOrClass
     * @return ModelManager
     */
    private static function getModelManager($objOrClass): ModelManager {
        if (is_object($objOrClass)) {
            $objOrClass = get_class($objOrClass);
        }
        if (!isset(self::$managers[$objOrClass])) {
            $modelManagers = get_property("models.managers");
            if (!isset($modelManagers[$objOrClass])) {
                throw new \RuntimeException("Model manager for class \"$objOrClass\" was not registered !!");
            }
            $modelManagerClass = $modelManagers[$objOrClass];
            self::$managers[$objOrClass] = new $modelManagerClass;
        }
        return self::$managers[$objOrClass];
    }

    /**
     * @param $model
     * @param array $options
     * @return mixed
     */
    public static function createModel($model, array $options = []) {
        return self::getModelManager($model)->create($model, $options);
    }

    /**
     * @param $model
     * @param array $options
     * @return mixed
     */
    public static function updateModel($model, array $options = []) {
        return self::getModelManager($model)->update($model, $options);
    }

    /**
     * @param $model
     * @param array $options
     * @return mixed
     */
    public static function deleteModel($model, array $options = []) {
        return self::getModelManager($model)->delete($model, $options);
    }

    /**
     * @param $modelClass
     * @param $modelId
     * @param array $options
     * @return mixed
     */
    public static function retrieveModelById($modelClass, $modelId, array $options = []) {
        return self::getModelManager($modelClass)->retrieveById($modelId, $options);
    }

    /**
     * @param $modelClass
     * @param array $options
     * @return mixed
     */
    public static function retrieveModels($modelClass, array $options = []) {
        return self::getModelManager($modelClass)->retrieve($options);
    }
}