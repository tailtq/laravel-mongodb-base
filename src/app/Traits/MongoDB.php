<?php

namespace App\Traits;

use MongoDB\Driver\Manager;
use MongoDB\Collection;

trait MongoDB
{
    protected $collectionName;

    /**
     * @param $collectionName
     * @return Collection|Manager
     */
    public function getCollection($collectionName)
    {
        $databaseName = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password =  env('DB_PASSWORD');
        $urlServer = env('DB_HOST') . ':' . env('DB_PORT');
        $manager = new Manager("mongodb://$username:$password@$urlServer/$databaseName?authSource=admin");

        return new Collection($manager, $databaseName, $collectionName);
    }

    /**
     * @param $condition
     * @return array
     */
    public function find($condition): array
    {
        $collection = $this->getCollection($this->collectionName);

        return $this->iterateToObjects($collection->find($condition));
    }

    /**
     * @param $conditionArr
     * @param array $options
     * @return array
     */
    public function aggregate($conditionArr, $options = []): array
    {
        $collection = $this->getCollection($this->collectionName);

        return $this->iterateToObjects($collection->aggregate($conditionArr, $options));
    }

    private function iterateToObjects($results)
    {
        $newResults = [];

        foreach ($results as $result) {
            $newResults[] = \MongoDB\BSON\toPHP(\MongoDB\BSON\fromPHP($result));
        }

        return $newResults;
    }
}
