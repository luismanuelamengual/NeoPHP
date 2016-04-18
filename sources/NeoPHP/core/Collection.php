<?php

namespace NeoPHP\core;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use NeoPHP\core\Object;
use NeoPHP\util\Arrayable;

class Collection extends Object implements Arrayable, ArrayAccess, IteratorAggregate, Countable
{
    protected $items;
    
    public function __construct (array $items = []) 
    {
        $this->items = $items;
    }
    
    public function put($key, $value)
    {
        $this->items[$key] = $value;
    }
    
    public function get($key)
    {
        return $this->items[$key];
    }
    
    public function getAt($index)
    {
        return $this->getValues()[$index]; 
    }
    
    public function getKeys()
    {
        return array_keys($this->items);
    }
    
    public function getValues()
    {
        return array_values($this->items);
    }

    public function add ($item)
    {
        array_push($this->items, $item);
    }
    
    public function addAll (Collection $items)
    {
        array_splice($this->items, count($this->items), 0, $items->toArray());
    }
    
    public function addFirst ($item)
    {
        array_unshift($this->items, $item);
    }
    
    public function addLast ($item)
    {
        array_push($this->items, $item);
    }
    
    public function getFirst ()
    {
        return reset($this->items);
    }
    
    public function getLast ()
    {
        return end($this->items);
    }
    
    public function insert($index, $item)
    {
        array_splice ($this->items, $index, 0, $item);
    }
    
    public function insertAll($index, Collection $items)
    {
        array_splice($this->items, count($this->items), $index, $items->toArray());
    }

    public function clear ()
    {
        $this->items = [];
    }
    
    public function remove ($item)
    {
        unset($this->items[$this->keyOf($item)]);
    }
    
    public function removeAt ($index)
    {   
        array_splice($this->items, $index, 1);
    }
    
    public function removeAll (Collection $items)
    {
        $this->filter(function($item) use ($items) { return !$items->contains($item); });
    }
    
    public function filter (callable $callable)
    {
        $this->items = array_filter($this->items, $callable);
    }
    
    public function sort (callable $callable)
    {
        uasort($this->items, $callable);
    }
    
    public function sortByKey (callable $callable)
    {
        uksort($this->items, $callable);
    }
    
    public function flip ()
    {
        $this->items = array_flip($this->items);
    }
    
    public function contains ($item)
    {
        return in_array($item, $this->items);
    }
    
    public function containsKey ($key)
    {
        return array_key_exists($key, $this->items);
    }
    
    public function containsAll (Collection $items)
    {
        return empty(array_diff($items->toArray(), $this->items));
    }
    
    public function size()
    {
        return $this->count();
    }
    
    public function isEmpty()
    {
        return empty($this->items);
    }
    
    public function indexOf ($item)
    {
        return array_search($item, array_values($this->items)); 
    }
    
    public function keyOf ($item)
    {
        return array_search($item, $this->items);
    }
    
    public function subList ($fromIndex, $toIndex)
    {
        return new Collection(array_slice($this->items, $fromIndex, $toIndex-$fromIndex));
    }
    
    public function count()
    {
        return count($this->items);
    }
    
    public function getIterator() 
    {
        return new ArrayIterator($this->items);
    }
    
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->items);
    }
    
    public function offsetGet($key)
    {
        return $this->items[$key];
    }
    
    public function offsetSet($key, $value)
    {
        if (is_null($key)) $this->items[] = $value; else $this->items[$key] = $value;
    }
    
    public function offsetUnset($key)
    {
        unset($this->items[$key]);
    }
    
    public function toArray()
    {
        return array_map(function ($value) { return $value instanceof Arrayable ? $value->toArray() : $value; }, $this->items);
    }
}