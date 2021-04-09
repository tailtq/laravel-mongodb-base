<?php

namespace App\Traits;

trait MongoDB
{
    public static function queryMongo($collection)
    {
        $databaseName = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password =  env('DB_PASSWORD');
        $urlServer = env('DB_HOST') . env('DB_PORT');

        $manager = new \MongoDB\Driver\Manager("mongodb://$username:$password@$urlServer/$databaseName?authSource=admin");

        $collection = new \MongoDB\Collection($manager, $databaseName, $collection);

        $results = $collection->find();
        return iterator_to_array($results);
    }
}
