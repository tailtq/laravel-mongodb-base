<?php

namespace Infrastructure;

use App\Helpers\CommonHelper;
use Carbon\Carbon;

class BaseRepository
{
    /**
     * @var \Illuminate\Database\Eloquent\Model $model;
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
        array $paginationOptions = ['perPage' => 10]
    ) {
        if (!$shouldPaginate) {
            return $this->model->where($condition)->get();
        }
        return $this->model->where($condition)->paginate($paginationOptions['perPage']);
    }

    /**
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function findById(int $id, array $columns = ['*'], array $relations = [])
    {
        return $this->model->select($columns)->with($relations)->find($id);
    }

    /**
     * @param array|closure $condition
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function findBy($condition)
    {
        return $this->model->where($condition)->first();
    }

    /**
     * @param array $data
     * @return
     */
    public function create(array $data)
    {
        if (CommonHelper::isAssociativeArray($data)) {
            $data['created_at'] = Carbon::now();
            $data['updated_at'] = Carbon::now();
        } else {
            foreach ($data as &$element) {
                $element['created_at'] = Carbon::now();
                $element['updated_at'] = Carbon::now();
            }
        }
        return $this->model->insertGetId($data);
    }

    /**
     * @param array|closure $condition
     * @param array $data
     * @return
     */
    public function updateBy($condition, array $data)
    {
        $data['updated_at'] = Carbon::now();

        return $this->model->where($condition)->update($data);
    }

    /**
     * @param array|closure $condition
     * @return
     */
    public function deleteBy($condition)
    {
        return $this->model->where($condition)->delete();
    }
}
