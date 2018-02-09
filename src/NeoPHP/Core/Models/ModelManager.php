<?php

namespace NeoPHP\Core\Models;

abstract class ModelManager {

    public abstract function create($model, array $parameters = []);

    public abstract function update($model, array $parameters = []);

    public abstract function delete($model, array $parameters = []);

    public abstract function retrieve(ModelQuery $modelQuery, array $parameters = []);
}