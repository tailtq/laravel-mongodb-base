<?php

use App\Models\Identity;
use App\Traits\RequestAPI;
use Faker\Factory;
use Illuminate\Database\Seeder;
use App\Traits\HandleUploadFile;

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
        $urls = $this->listFiles('identity_cards');
        $faker = Factory::create();

        foreach ($urls as $index => $url) {
            $chunks = explode('/', $url);
            $name = str_replace(['%20', '_'], ' ', $chunks[count($chunks) - 1]);
            $name = str_replace(['.JPG', 'passport', 'GPLX'], '', $name);

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
            ], $this->getDefaultHeaders());

            if (!$response->status) {
                continue;
            }

            $data['mongo_id'] = $response->body->_id;
            $data['images'] = array_map(function ($index, $element) use ($response) {
                return [
                    'url' => $element,
                    'mongo_id' => $response->body->facial_data[$index]->face_id
                ];
            }, array_keys($data['images']), $data['images']);

            Identity::create($data);
        }
    }

    private function randomCardNumber($faker)
    {
        return implode('', $faker->randomElements(['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'], 9, true));
    }
}
