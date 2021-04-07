<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Modules\User\Models\User;

class UserSeeder extends Seeder
{
    use \App\Traits\RequestAPI;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => '123',
        ];

        User::insert([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'created_at' => dateNow()
        ]);
    }
}
