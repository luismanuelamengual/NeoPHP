<?php

namespace NeoPHP\mvc\manager;

use NeoPHP\app\ApplicationComponent;
use NeoPHP\core\Collection;
use NeoPHP\mvc\Model;
use NeoPHP\mvc\ModelFilter;
use NeoPHP\mvc\ModelSorter;
use NeoPHP\mvc\MVCApplication;
use NeoPHP\util\IntrospectionUtils;
use NeoPHP\util\logging\Logger;
use NeoPHP\util\properties\PropertiesManager;

abstract class ModelManager extends ApplicationComponent
{
    const PARAMETER_START = "start";
    const PARAMETER_LIMIT = "limit";
    
    private $modelClass;
    
    /**
     * Constructor del manager de modelos
     * @param MVCApplication $application Aplicación mvc al cual pertenece
     * @param type $modelClass Clase de modelo con la que trabaja
     */
    public function __construct (MVCApplication $application, $modelClass)
    {
        parent::__construct($application);
        $this->modelClass = $modelClass;
    }
    
    /**
     * Obtiene el nombre de la clase con la cual trabaja el manager
     * @return string Nombre de la clase de modelo con la que el manager trabaja
     */
    protected final function getModelClass ()
    {
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
     * Crea un modelo a traves de sus propiedades
     * @param type $modelClass Clase del modelo que se desea obtener
     * @param type $properties propiedades del modelos
     */
    protected final function createModel ($modelClass, array $properties = [])
    {
        return $this->getManager($modelClass)->create($properties);
    }
    
    /**
     * Obtiene un modelo a través de su id
     * @param type $modelClass Clase del modelo que se desea obtener
     * @param type $id Id del modelo
     */
    protected final function findModel ($modelClass, $id)
    {
        return $this->getManager($modelClass)->findById($id);
    }
    
    /**
     * Obtiene todos los modelos con las opciones establecidas
     * @param type $modelClass Clase del modelo que se desea obtener
     * @param ModelFilter $filters Filtros a aplicar para la obtención de los modelos
     * @param ModelSorter $sorters Ordenamientos a aplicar para los modelos
     * @param array $parameters Parametros extra para la obtención de modelos
     * @return Collection Lista de modelo obtenidos
     */
    protected final function findModels ($modelClass, array $filters=[], array $sorters=[], array $parameters=[])
    {
        return $this->getManager($modelClass)->find($filters, $sorters, $parameters);
    }
    
    /**
     * Crea un modelo establecido
     * @param Model $model modelo a crearse
     * @return boolean Indica si se creo o no el modelo
     */
    protected final function insertModel (Model $model)
    {
        return $this->getManager(get_class($model))->insert($model);
    }
    
    /**
     * Actualiza un modelo
     * @param Model $model modelo a actualizar
     * @return boolean Indica si el modelo se actualizo o no
     */
    protected final function updateModel (Model $model)
    {
        return $this->getManager(get_class($model))->update($model);
    }
    
    /**
     * Borra un modelo
     * @param Model $model modelo a borrar
     * @return boolean indica si el modelo se borró o no
     */
    protected final function removeModel (Model $model)
    {
        return $this->getManager(get_class($model))->remove($model);
    }
    
    /**
     * Obtiene un modelo a partir de su id
     * @param type $id Id del modelo a obtener
     * @return Model modelo obtenido
     */
    public final function findById ($id)
    {
        $model = null;
        $modelCollection =  $this->find(["id"=>$id]);
        if ($modelCollection != null && $modelCollection instanceof Collection)
        {
            $model = $modelCollection->getFirst();
        }
        return $model;
    }
    
    /**
     * Crea un nuevo modelo a partir de las propiedades establecidas
     * @param array $properties
     * @return modelClass
     */
    public function create (array $properties = [])
    {
        $modelClass = $this->getModelClass();
        $model = new $modelClass;
        IntrospectionUtils::setRecursivePropertyValues($model, $properties);
        return $model;
    }
    
    public abstract function find (array $filters=[], array $sorters=[], array $parameters=[]);
    public abstract function insert (Model $model);
    public abstract function update (Model $model);
    public abstract function remove (Model $model);
}