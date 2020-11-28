<?php

namespace App\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DatabaseHelper
{
    public static function updateMultiple($data, $conditionColumn, $table)
    {
        /*
         * UPDATE movements SET
                movement_miles = CASE movement_id
                    WHEN 278 THEN 3.50
                    WHEN 279 THEN 0.00
                    WHEN 280 THEN 0.00
                END,
                movement_km = CASE movement_id
                    WHEN 278 THEN 5.63
                    WHEN 279 THEN 0.00
                    WHEN 280 THEN 0.00
                END
                WHERE movement_id IN (278,279,280)
        */
        if (count($data) === 0) {
            return;
        }
        $keys = array_keys($data[0]);
        $keys = array_filter($keys, function ($key) use ($conditionColumn) {
            return $key !== $conditionColumn;
        });

        $query = "UPDATE `$table` SET ";
        $condition = implode(', ', Arr::pluck($data, $conditionColumn));
        $sets = [];

        foreach ($keys as $key) {
            $set = "`$key` = CASE `$conditionColumn` ";

            foreach ($data as $row) {
                $condition = $row[$conditionColumn];
                $value = in_array(gettype($row[$key]), ['string', 'object']) ? "'$row[$key]'" : $row[$key];
                $value = $value === null ? 'NULL' : $value;

                $condition = is_string($condition) ? "'$condition'" : $condition;
                $set .= "WHEN $condition THEN $value ";
            }
            $set .= ' END';
            $sets[] = $set;
        }

        $query .= implode(', ', $sets);
        $query .= " WHERE `$conditionColumn` IN ($condition)";

        DB::statement($query);
    }
}
