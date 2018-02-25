<?php

namespace NeoPHP\Models;

abstract class ModelManager {

    public abstract function create($model, array $options = []);

    public abstract function update($model, array $options = []);

    public abstract function delete($model, array $options = []);

    public abstract function retrieveById ($modelId, array $options = []);

    public abstract function retrieve(array $options = []);
}