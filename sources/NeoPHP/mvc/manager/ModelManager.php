<?php

namespace NeoPHP\mvc\manager;

use NeoPHP\app\ApplicationComponent;
use NeoPHP\core\Collection;
use NeoPHP\core\reflect\ReflectionAnnotatedClass;
use NeoPHP\mvc\Model;
use NeoPHP\mvc\MVCApplication;
use NeoPHP\util\IntrospectionUtils;
use NeoPHP\util\logging\Logger;
use NeoPHP\util\properties\PropertiesManager;
use Throwable;

abstract class ModelManager extends ApplicationComponent
{
    const OPTION_FILTERS = "filters";
    const OPTION_SORTERS = "sorters";
    const OPTION_START = "start";
    const OPTION_LIMIT = "limit";
    const OPTION_COMPLETE = "complete";
    
    private $modelClassName;
    private $modelClass;
    
    /**
     * Constructor del manager de modelos
     * @param MVCApplication $application Aplicación mvc al cual pertenece
     * @param type $modelClassName Clase de modelo con la que trabaja
     */
    public function __construct (MVCApplication $application, $modelClassName)
    {
        parent::__construct($application);
        $this->modelClassName = $modelClassName;
    }
    
    /**
     * Obtiene el nombre de la clase con la cual trabaja el manager
     * @return string Nombre de la clase de modelo con la que el manager trabaja
     */
    protected final function getModelClassName ()
    {
        return $this->modelClassName;
    }
    
    /**
     * Obtiene el nombre de la clase con la cual trabaja el manager
     * @return ReflectionAnnotatedClass Nombre de la clase de modelo con la que el manager trabaja
     */
    protected final function getModelClass ()
    {
        if (!isset($this->modelClass))
        {
            $this->modelClass = new ReflectionAnnotatedClass($this->modelClassName);
        }
        return $this->modelClass;
    }
    
    /**
     * Obtiene el manager de propiedades de la aplicación
     * @return PropertiesManager Propiedades de la aplicación
     */
    protected final function getProperties ()
    {
        return $this->application->getProperties();
    }
    
    /**
     * Obtiene el logger de la aplicación
     * @return Logger Logger de la aplicación
     */
    protected final function getLogger ()
    {
        return $this->application->getLogger();
    }
    
    /**
     * Obtiene el manejador de modelos
     * @param type $modelClass
     * @return ModelManager Manejador de modelos
     */
    protected final function getManager ($modelClass)
    {
        return $this->application->getManager($modelClass);
    }
    
    /**
     * Obtiene un modelo a través de su id
     * @param type $modelClass Clase del modelo que se desea obtener
     * @param type $id Id del modelo
     */
    protected final function findModel ($modelClass, $id, array $options=[])
    {
        return $this->getManager($modelClass)->findById($id, $options);
    }
    
    /**
     * Obtiene todos los modelos con las opciones establecidas
     * @param type $modelClass Clase del modelo que se desea obtener
     * @param array $options Parametros extra para la obtención de modelos
     * @return Collection Lista de modelo obtenidos
     */
    protected final function findModels ($modelClass, array $options=[])
    {
        return $this->getManager($modelClass)->find($options);
    }
    
    /**
     * Crea un modelo establecido
     * @param Model $model modelo a crearse
     * @return boolean Indica si se creo o no el modelo
     */
    protected final function insertModel (Model $model, array $options=[])
    {
        return $this->getManager(get_class($model))->insert($model, $options);
    }
    
    /**
     * Actualiza un modelo
     * @param Model $model modelo a actualizar
     * @return boolean Indica si el modelo se actualizo o no
     */
    protected final function updateModel (Model $model, array $options=[])
    {
        return $this->getManager(get_class($model))->update($model, $options);
    }
    
    /**
     * Borra un modelo
     * @param Model $model modelo a borrar
     * @return boolean indica si el modelo se borró o no
     */
    protected final function removeModel (Model $model, array $options=[])
    {
        return $this->getManager(get_class($model))->remove($model, $options);
    }
    
    /**
     * Obtiene un modelo a partir de su id
     * @param type $id Id del modelo a obtener
     * @return Model modelo obtenido
     */
    public final function findById ($id, array $options=[])
    {
        $model = null;
        $modelCollection =  $this->find(array_merge($options, [self::OPTION_FILTERS=>["id"=>$id]]));
        if ($modelCollection != null && $modelCollection instanceof Collection)
        {
            $model = $modelCollection->getFirst();
        }
        return $model;
    }
    
    /**
     * Crea un modelo a traves de sus propiedades
     * @param type $modelClass Clase del modelo que se desea obtener
     * @param type $properties propiedades del modelos
     */
    protected final function createModel ($modelClass, array $properties = [])
    {
        return $this->getManager($modelClass)->create($properties);
    }
    
    /**
     * Crea un nuevo modelo a partir de las propiedades establecidas
     * @param array $properties
     * @return modelClass
     */
    public function create (array $properties = [])
    {
        $modelClass = $this->getModelClassName();
        $model = new $modelClass;
        foreach ($properties as $property => $value)
        {
            try
            {
                IntrospectionUtils::setRecursivePropertyValue($model, $property, $value);
            } catch (Throwable $ex) {}
        }
        return $model;
    }
    
    public abstract function find (array $options=[]);
    public abstract function insert (Model $model, array $options=[]);
    public abstract function update (Model $model, array $options=[]);
    public abstract function remove (Model $model, array $options=[]);
}