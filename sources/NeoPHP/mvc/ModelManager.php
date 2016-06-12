<?php

namespace NeoPHP\mvc;

use NeoPHP\app\ApplicationComponent;
use NeoPHP\core\Collection;
use NeoPHP\util\logging\Logger;
use NeoPHP\util\properties\PropertiesManager;

abstract class ModelManager extends ApplicationComponent
{
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
     * Obtiene un modelo a través de su id
     * @param type $modelClass Clase del modelo que se desea obtener
     * @param type $id Id del modelo
     */
    protected final function retrieveModel ($modelClass, $id)
    {
        return $this->getManager($modelClass)->retrieveById($id);
    }
    
    /**
     * Obtiene todos los modelos con las opciones establecidas
     * @param type $modelClass Clase del modelo que se desea obtener
     * @param ModelFilter $filters Filtros a aplicar para la obtención de los modelos
     * @param ModelSorter $sorters Ordenamientos a aplicar para los modelos
     * @param array $parameters Parametros extra para la obtención de modelos
     * @return Collection Lista de modelo obtenidos
     */
    protected final function retrieveModels ($modelClass, ModelFilter $filters=null, ModelSorter $sorters=null, array $parameters=[])
    {
        return $this->getManager($modelClass)->retrieve($filters, $sorters, $parameters);
    }
    
    /**
     * Persiste un modelo
     * @param Model $model modelo a persistir
     * @return boolean indica si se persistió o no
     */
    protected final function persistModel (Model $model)
    {
        return $this->getManager(get_class($model))->persist($model);
    }
    
    /**
     * Crea un modelo establecido
     * @param Model $model modelo a crearse
     * @return boolean Indica si se creo o no el modelo
     */
    protected final function createModel (Model $model)
    {
        return $this->getManager(get_class($model))->create($model);
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
    protected final function deleteModel (Model $model)
    {
        return $this->getManager(get_class($model))->delete($model);
    }
    
    /**
     * Persiste un modelo en función del id del modelo, si no tiene id se creara
     * el modelo y si tiene se actualizará
     * @param Model $model Modelo a persistir
     */
    public final function persist (Model $model)
    {
        $result = null;
        $modelId = $model->getId();
        if (isset($modelId))
        {
            $result = $this->update($model);
        }
        else
        {
            $result = $this->create($model);
        }
        return $result;
    }
    
    /**
     * Obtiene un modelo a partir de su id
     * @param type $id Id del modelo a obtener
     * @return Model modelo obtenido
     */
    public final function retrieveById ($id)
    {
        $model = null;
        $modelCollection =  $this->retrieve(new PropertyModelFilter("id", $id));
        if ($modelCollection != null && $modelCollection instanceof Collection)
        {
            $model = $modelCollection->getFirst();
        }
        return $model;
    }
    
    public abstract function retrieve (ModelFilter $filters=null, ModelSorter $sorters=null, array $parameters=[]);
    public abstract function create (Model $model);
    public abstract function update (Model $model);
    public abstract function delete (Model $model);
}