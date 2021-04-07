<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

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
        $response = $this->sendPOSTRequest(config('app.ai_server') . '/users/register', $data, $this->getDefaultHeaders());

        if (!$response->status) {
            var_dump($response);
            throw new Error($response->message);
        }

        DB::table('users')->insert([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'created_at' => Carbon::now(),
        ]);
    }
}
