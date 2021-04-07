<?php

use App\Traits\RequestAPI;
use Faker\Factory;
use Illuminate\Database\Seeder;
use App\Traits\HandleUploadFile;
use Illuminate\Http\Request;

class IdentitySeeder extends Seeder
{
    use HandleUploadFile, RequestAPI;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $token = $this->getAIToken();
        $urls = $this->listFiles('identity_cards');
        $faker = Factory::create();

        foreach ($urls as $index => $url) {
            $chunks = explode('/', $url);
            $name = str_replace(['%20', '_'], ' ', $chunks[count($chunks) - 1]);
            $name = str_replace(['.JPG', 'passport', 'GPLX', '.png', '.jpg'], '', $name);
            $name = trim($name);

            $data = [
                'name' => $name,
                'status' => !empty($data['status']) ? 'tracking' : 'untracking',
                'card_number' => 'FAKE' . $this->randomCardNumber($faker),
                'images' => [$url],
            ];

            $response = $this->sendPOSTRequest(config('app.ai_server') . '/identities', [
                'name' => $data['name'],
                'status' => !empty($data['status']) ? 'tracking' : 'untracking',
                'card_number' => $data['card_number'],
                'images' => $data['images'],
            ], $this->getDefaultHeaders($token));

            $status = $response->status ? 'Created' : 'Failed';
            echo "$name: $status\n";
            continue;
        }
    }

    protected function getAIToken()
    {
        $response = $this->sendPOSTRequest(config('app.ai_server') . '/users/login', [], [
            'X-API-KEY' => config('app.ai_api_key'),
            'Authorization' => 'Basic ' . base64_encode('admin@gmail.com:123')
        ]);

        return $response->body->token;
    }

    private function randomCardNumber($faker)
    {
        return implode('', $faker->randomElements(['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'], 9, true));
    }
}
