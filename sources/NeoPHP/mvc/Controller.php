<?php

namespace NeoPHP\mvc;

use Exception;
use NeoPHP\app\ApplicationComponent;
use NeoPHP\core\Collection;
use NeoPHP\core\IllegalArgumentException;
use NeoPHP\mvc\Controller;
use NeoPHP\mvc\ModelManager;
use NeoPHP\mvc\MVCApplication;
use NeoPHP\mvc\NoRouteException;
use NeoPHP\mvc\TemplateView;
use NeoPHP\mvc\View;
use NeoPHP\util\logging\Logger;
use NeoPHP\util\properties\PropertiesManager;
use ReflectionFunction;
use ReflectionMethod;

abstract class Controller extends ApplicationComponent
{
    const ANNOTATION_ACTION = "action";
    const ANNOTATION_PARAMETER_NAME = "name";
    
    public function __construct (MVCApplication $application)
    {
        parent::__construct($application);
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
     * @param string $modelClass
     * @return ModelManager Manejador de modelos
     */
    protected final function getManager ($modelClass)
    {
        return $this->application->getManager($modelClass);
    }
    
    /**
     * Obtiene un controlador para un clase dada
     * @param $controllerClass clase de controlador a obtener
     * @return Controller Controlador
     * @throws IllegalArgumentException
     */
    protected final function getController ($controllerClass)
    {
        return $this->application->getController($controllerClass);
    }
    
    /**
     * Crea una vista a partir de una clase
     * @param $viewClass Claes de la vista a crear
     * @return View Vista a ser obtenida
     * @throws IllegalArgumentException
     */
    protected final function createView ($viewClass)
    {
        return $this->application->createView($viewClass);
    }
    
    /**
     * Crea una vista de template
     * @param $templateName nombre del template a renderizar
     * @return TemplateView Vista de template
     */
    protected final function createTemplateView ($templateName, array $parameters = [])
    {
        return $this->application->createTemplateView($templateName, $parameters);
    }
    
    /**
     * Ejecuta una acción en el controlador con los parámetros establecidos
     * @param string $action Acción a ejecutar
     * @param array $parameters Parámetros de la acción
     * @return type Resultado de la ejecución
     * @throws Exception Error en la ejecución de la acción
     */    
    public function executeAction ($action, array $parameters = [])
    {
        if (empty($action))
            $action = "index";
        
        $response = false;
        try
        {
            if ($this->onBeforeAction($action, $parameters) == true)
            {
                $response = $this->onAction ($action, $parameters);
                $response = $this->onAfterAction ($action, $parameters, $response);
            }
        }
        catch (Exception $ex)
        {
            if (method_exists($this, "onActionError"))
                $this->onActionError($action, $ex);
            else
                throw $ex;
        }
        return $response;
    }
    
    protected function onBeforeAction ($action, $parameters)
    {  
        return true;
    }
    
    protected function onAction ($action, $parameters)
    {
        $controllerMethod = $this->getMethodForAction($action);
        if ($controllerMethod == null)
        {
            throw new NoRouteException("Controller method for action \"$action\" not found in controller \"" . get_class($this) . "\"");
        }
        if (!method_exists($this, $controllerMethod))
        {
            throw new NoRouteException("Controller method \"$controllerMethod\" not found in controller \"" . get_class($this) . "\"");
        }
        return $this->callMethod($controllerMethod, $parameters);
    }
    
    protected function onAfterAction ($action, $parameters, $response)
    {
        if (!empty($response))
        {
            if ($response instanceof View)
            {
                $response->render();
            }
            else if (is_object($response))
            {
                print json_encode($response);
            }
            else
            {
                print $response;
            }
        }
        return $response;
    }
    
    /**
     * Obtiene el método dentro del controlador que tiene que llamar al ejecutar
     * la acción establecida
     * @param string $action Acción ejecutada
     * @return string Nombre del método en el controlador
     */
    private function getMethodForAction ($action)
    {
        $methodName = null;
        if (isset($this->getProperties()->useControllerAnnotations))
        {
            foreach ($this->getClass()->getMethods() as $method)
            {
                $annotations = $method->getAnnotations (self::ANNOTATION_ACTION);
                if (!empty($annotations))
                {
                    foreach ($annotations as $annotation)
                    {
                        $methodAction = $annotation->getParameter (self::ANNOTATION_PARAMETER_NAME);
                        if (empty($methodAction))
                            $methodAction = "index";

                        if ($action == $methodAction || (empty($action) && empty($methodAction)))
                        {
                            $methodName = $method->getName();
                            break;
                        }
                    }
                }
            }
        }
        else
        {
            $methodName = $action . "Action";
        }
        return $methodName;
    }
    
    /**
     * Llama internamente a un metodo interno del controlador pasandole como
     * argumentos al metodo los parámetros establecidos
     * @param string $method Nombre del método a llamar
     * @param array $parameters Parámetros a pasar al método
     * @return type respuesta obtenida del método
     */
    private function callMethod ($method, array $parameters=[])
    {
        $parameterIndex = 0;
        $callable = [$this, $method];
        $callableParameters = array();
        $callableData = is_array($callable)? (new ReflectionMethod($callable[0],$callable[1])) : (new ReflectionFunction($callable));
        foreach ($callableData->getParameters() as $parameter)
        {
            $parameterName = $parameter->getName();
            $parameterValue = null;
            if (array_key_exists($parameterName, $parameters))
                $parameterValue = $parameters[$parameterName];
            else if (array_key_exists($parameterIndex, $parameters))
                $parameterValue = $parameters[$parameterIndex];
            if ($parameterValue == null && $parameter->isOptional())
                $parameterValue = $parameter->getDefaultValue();
            $callableParameters[] = $parameterValue;
            $parameterIndex++;
        }
        return call_user_func_array($callable, $callableParameters);
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
    protected final function retrieveModels ($modelClass, array $filters=[], array $sorters=[], array $parameters=[])
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
}