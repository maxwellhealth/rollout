<?php

namespace Opensoft\Rollout\Storage;

/**
 * Storage adapter using MongoDB
 *
 * @author James Hrisho <@securingsincity>
 */
class MongoDBStorageAdapter implements StorageInterface
{

    /**
     * @var object
     */
    private $mongo;

    /**
     * @var string
     */
    private $collection;

    private $cache = [];
    public function __construct(\MongoDB $mongo, $collection = "rollout_feature")
    {
        $this->mongo = $mongo;
        $this->collection = $collection;

    }
    public function getCollectionName()
    {
        return $this->collection;
    }

    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    public function resetCache()
    {
        $this->cache = [];
    }

    public function getFromCache($key)
    {
        if (empty($this->cache)) {
            return null;
        }
        if (isset($this->cache[$key]) && $item = $this->cache[$key]) {
            return $item;
        }
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        if ($item = $this->getFromCache($key)) {
            return $item['value'];
        }
        $collection = $this->getCollectionName();
        $result = $this->mongo->$collection->find();

        if ($result->count() === 0) {
            return null;
        }

        $result = iterator_to_array($result);


        $toggles = array_reduce($result, function($acc, $toggle) {
            $acc[$toggle['name']] = $toggle;
            return $acc;
        }, []);
        $this->setCache($toggles);
        $item = $this->getFromCache($key);
        if (!isset($item)) {
            return null;
        }

        return $item['value'];
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        $collection = $this->getCollectionName();
        $this->resetCache();
        $this->mongo->$collection->update(['name' => $key], ['$set' => ['value' => $value]], ['upsert' => true]);
    }

    /**
     * @inheritdoc
     */
    public function remove($key)
    {
        $this->resetCache();
        $collection = $this->getCollectionName();
        $this->mongo->$collection->remove(['name' => $key]);
    }

}
