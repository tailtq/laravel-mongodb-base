<?php

namespace Infrastructure;

use App\Helpers\CommonHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\UTCDateTime;

class BaseRepository
{
    /**
     * @var Model $model ;
     */
    protected $model;

    public function __construct(string $model)
    {
        // dynamically switch between Eloquent and Query Builder
        // clone $this->model when using
        $this->model = app($model);
    }

    public function listBy(
        $condition = [],
        $shouldPaginate = true,
        array $paginationOptions = ['perPage' => 10],
        array $orderBy = [['created_at', 'desc']]
    )
    {
        $query = gettype($condition) == 'object'
            ? $condition($this->model)
            : $this->model->where($condition);

        if (!$shouldPaginate) {
            return $query->get();
        }
        if (count($orderBy) > 0) {
            foreach ($orderBy as [$column, $ascendingTYpe]) {
                $query = $query->orderBy($column, $ascendingTYpe);
            }
        }
        return $query->paginate($paginationOptions['perPage']);
    }

    /**
     * @param string $id
     * @param array $columns
     * @param array $relations
     * @return Model
     */
    public function findById(string $id, array $columns = ['*'], array $relations = [])
    {
        return $this->model->select($columns)->with($relations)->find($id);
    }

    /**
     * @param array|\Closure $condition
     * @return Model
     */
    public function findBy($condition)
    {
        return $this->model->where($condition)->first();
    }

    /**
     * @param array $data
     * @param bool $indexedReturningId
     * @return int|array|bool
     */
    public function create(array $data, bool $indexedReturningId = false)
    {
        if (CommonHelper::isAssociativeArray($data)) {
            $data['created_at'] = dateNow();
            $data['updated_at'] = dateNow();
        } else {
            $ids = [];

            foreach ($data as &$element) {
                $element['created_at'] = dateNow();
                $element['updated_at'] = dateNow();

                if ($indexedReturningId) {
                    $ids[] = $this->model->insertGetId($element);
                }
            }
            if ($indexedReturningId) {
                return $ids;
            }
            return $this->model->insert($data);
        }

        return $this->model->insertGetId($data);
    }

    /**
     * @param array|\Closure $condition
     * @param array $data
     * @return
     */
    public function updateBy($condition, array $data)
    {
        $data['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');

        return $this->model->where($condition)->update($data);
    }

    /**
     * @param string $conditionColumn
     * @param array $data
     */
    public function updateMany(string $conditionColumn, array $data)
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
        $table = $this->model->getTable();
        $keys = array_keys($data[0]);
        $keys = array_filter($keys, function ($key) use ($conditionColumn) {
            return $key !== $conditionColumn;
        });

        $query = "UPDATE `$table` SET ";
        $sets = [];
        $totalConditionIsString = false;

        foreach ($keys as $key) {
            $set = "`$key` = CASE `$conditionColumn` ";
            $updatingString = "";

            foreach ($data as $row) {
                $condition = $row[$conditionColumn];
                $value = in_array(gettype($row[$key]), ['string', 'object']) ? "'$row[$key]'" : $row[$key];
                $value = $value === null ? 'NULL' : $value;

                $condition = is_string($condition) ? "'$condition'" : $condition;
                $totalConditionIsString = is_string($condition);

                if ($value !== "'false'") {
                    $updatingString .= "WHEN $condition THEN $value ";
                }
            }
            if ($updatingString) {
                $set .= $updatingString;
                $set .= ' END';
                $sets[] = $set;
            }
        }

        if ($totalConditionIsString) {
            foreach ($data as &$element) {
                $element[$conditionColumn] = '"' . $element[$conditionColumn] . '"';
            }
        }
        $totalCondition = implode(', ', Arr::pluck($data, $conditionColumn));
        $query .= implode(', ', $sets);
        $query .= " WHERE `$conditionColumn` IN ($totalCondition)";

        DB::statement($query);
    }

    /**
     * @param array|\Closure $condition
     * @return
     */
    public function deleteBy($condition)
    {
        return $this->model->where($condition)->delete();
    }
}
