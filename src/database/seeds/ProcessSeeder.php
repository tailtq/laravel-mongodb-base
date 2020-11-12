<?php

use Faker\Factory;
use Carbon\Carbon;
use App\Models\Process;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProcessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [];
        $faker = Factory::create();
        $users = DB::table('users')->get();

        foreach ($users as $user) {
            array_push($data, [
                'user_id' => $user->id,
                'name' => 'Presidential election 2020',
                'video_url' => 'https://www.youtube.com/watch?v=I8QtLm6GEd4&ab_channel=CNBCTelevision',
                'thumbnail' => 'https://i.imgur.com/MwFcNqZ.png',
                'description' => $faker->realText(50),
                'status' => Process::STATUS['stopped'],
                'created_at' => Carbon::now()
            ]);
        }

        Process::insert($data);
    }
}
