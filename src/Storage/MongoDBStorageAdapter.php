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

    public function __construct($mongo, $collection = "rollout_feature")
    {
        $this->mongo = $mongo;

        if ($collection) {
            $this->collection = $collection;
        }
    }
    public function getCollectionName()
    {
        return $this->collection;
    }
    /**
     * @inheritdoc
     */
    public function get($key)
    {
        $collection = $this->collection;
        $result = $this->mongo->$collection->findOne(['name' => $key]);

        if (!$result) {
            return null;
        }

        return $result['value'];
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        $collection = $this->collection;
        $this->mongo->$collection->update(['name' => $key], ['$set' => ['value' => $value]]);
    }

    /**
     * @inheritdoc
     */
    public function remove($key)
    {
        $collection = $this->collection;
        $this->mongo->$collection->remove(['name' => $key]);
    }

}
