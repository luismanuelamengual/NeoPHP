<?php
namespace NeoPHP\Nodes;

use RuntimeException;
use NeoPHP\Http\Response;

abstract class Nodes {

    private static $nodes = [];

    public static function get(string $nodeName=null) : Node {
        if (!isset(self::$nodes[$nodeName])) {
            if (get_property("resources.remote_url")) {
                throw new RuntimeException("Property \"resources.remote_url\" is deprecated !!, please use .env file and change APP_NAME to a value different to \"site5\" !!", Response::HTTP_VERSION_NOT_SUPPORTED);
            }
            $nodeConfig = null;
            if (is_null($nodeName)) {
                $nodeName = get_property("nodes.default");
            }
            if (is_null($nodeName) || $nodeName == get_property("app.name")) {
                self::$nodes[$nodeName] = new LocalNode();
            }
            else {
                $nodes = get_property("nodes.nodes");
                if (empty($nodes)) {
                    throw new RuntimeException("Nodes list is Empty !!", Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                if (!isset($nodes[$nodeName])) {
                    throw new RuntimeException("Node \"$nodeName\" not found !!", Response::HTTP_NOT_FOUND);
                }
                $nodeConfig = $nodes[$nodeName];
                self::$nodes[$nodeName] = new RemoteNode($nodeConfig);
            }
        }
        return self::$nodes[$nodeName];
    }
}