<?php

namespace NeoPHP\Resources;

use Curl\Curl;
use Exception;
use RuntimeException;
use NeoPHP\Database\Query\DeleteQuery;
use NeoPHP\Database\Query\InsertQuery;
use NeoPHP\Database\Query\Query;
use NeoPHP\Database\Query\SelectQuery;
use NeoPHP\Database\Query\UpdateQuery;

/**
 * Class RemoteResource
 * @package NeoPHP\Resources
 */
class RemoteResourceManager extends ResourceManager {

    private $remoteUrl;

    /**
     * Obtiene la url remota donde se sirve el recurso
     * @return string
     */
    public function getRemoteUrl(): string {
        return $this->remoteUrl;
    }

    /**
     * Establece la url remota donde se sirve el recurso
     * @param string $remoteUrl
     */
    public function setRemoteUrl(string $remoteUrl) {
        $this->remoteUrl = $remoteUrl;
    }

    /**
     * Metodo para buscar recursos
     * @param SelectQuery $query
     * @return mixed
     * @throws \ErrorException
     */
    public function find(SelectQuery $query) {
        return $this->getRemoteContents($query, 'GET');
    }

    /**
     * Método para insertar un nuevo recurso
     * @param InsertQuery $query
     * @return mixed
     * @throws \ErrorException
     */
    public function insert(InsertQuery $query) {
        return $this->getRemoteContents($query, 'PUT');
    }

    /**
     * Método para actualizar un recurso
     * @param UpdateQuery $query
     * @return mixed
     * @throws \ErrorException
     */
    public function update(UpdateQuery $query) {
        return $this->getRemoteContents($query, 'POST');
    }

    /**
     * Método para borrar un recurso
     * @param DeleteQuery $query
     * @return mixed
     * @throws \ErrorException
     */
    public function delete(DeleteQuery $query) {
        return $this->getRemoteContents($query, 'DELETE');
    }

    /**
     * @param Query $query
     * @param $method
     * @return null
     * @throws \ErrorException
     */
    private function getRemoteContents (Query $query, $method) {
        $session = get_session();
        $parameters = [
            $session->name() => $session->id(),
            'rawQuery' => serialize($query),
            'debug' => get_property("app.debug"),
            'returnException' => true
        ];
        $session->closeWrite();
        $curl = new Curl();
        $curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
        switch ($method) {
            case 'GET':
                $curl->setOpt(CURLOPT_CUSTOMREQUEST, 'GET');
                $curl->setOpt(CURLOPT_RETURNTRANSFER, true);
                $curl->setOpt(CURLOPT_POST, true);
                $curl->setOpt(CURLOPT_POSTFIELDS, $curl->buildPostData($parameters));
                $curl->setUrl($this->remoteUrl);
                $curl->exec();
                break;
            case 'PUT':
                $curl->put($this->remoteUrl, $parameters);
                break;
            case 'POST':
                $curl->post($this->remoteUrl, $parameters);
                break;
            case 'DELETE':
                $curl->delete($this->remoteUrl, $parameters);
                break;
        }

        if ($curl->error) {
            if (!empty($curl->response)) {
                $remoteException = unserialize($curl->response);
                if ($remoteException instanceof Exception) {
                    throw new RuntimeException("Remote exception - " . $curl->errorMessage, $curl->errorCode, $remoteException);
                }
            }
            throw new RuntimeException("Remote exception - " . $curl->errorMessage, $curl->errorCode);
        }
        $session->start();
        return $curl->response;
    }
}