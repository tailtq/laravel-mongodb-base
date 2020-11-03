<?php

use Faker\Factory;
use App\Models\Identity;
use Illuminate\Database\Seeder;

class IdentitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();

        $identityCards = [
            'https://www.visaliendaiduong.com/Uploaded/Users/van.nguyen/images/2019/CMND/CMND-visaliendaiduong_om-4.jpg',
            'https://thamtuphuctam.com/wp-content/uploads/2020/01/tra-cuu-thong-tin-ca-nhan-tu-cmt-cmnd-can-cuoc.jpg',
            'https://lamsohongchatluong.com/wp-content/uploads/2020/04/lam-chung-minh-nhan-dan-cmnd-gia-0.jpg',
            'https://www.upsieutoc.com/images/2020/04/06/imagef096ffa5d11770bf.png',
            'https://1.bp.blogspot.com/-wp4PPXyX4qg/Vvh-gQjqN4I/AAAAAAAAHPo/cFL_zsZi27YvEbX2x2U46YPWM7sOY1FIg/s1600/CMND.jpg',
            'https://thamtutantinh.com/wp-content/uploads/2020/04/D%E1%BB%8Bch-v%E1%BB%A5-tra-c%E1%BB%A9u-th%C3%B4ng-tin-c%C3%A1-nh%C3%A2n-t%E1%BB%AB-CMND-c%C4%83n-c%C6%B0%E1%BB%9Bc.jpg',
            'https://nld.mediacdn.vn/2014/5-h2-0-2fa4d.jpg',
            'https://1.bp.blogspot.com/-LB5RSV350Q4/WWGP4FAedLI/AAAAAAAABrM/yk5eC1dsPvkOz23o-S5Zj0yqAtOBRLXTgCLcBGAs/s1600/cp72h.jpg',
            'https://lamgiayto.net/wp-content/uploads/2020/04/images597493_cmnd-6.jpg',
            'https://thibanglaixe24h.net/wp-content/uploads/2017/10/bang-lai-xe-may-a1.jpg',
            'https://daotaothanhcong.com/wp-content/uploads/2019/10/bang-lai-xe-b1-co-thoi-han-bao-lau.jpg',
            'https://3.bp.blogspot.com/-2kUiXdV7HhU/W06-JUOxopI/AAAAAAAABq4/acV7taEKWAEH-bO4QSPiFsQSXTntZwS2gCLcBGAs/s1600/giay-phep-lai-xe.jpg',
            'https://dulichviet.com.vn/images/2012/11/xin-visa-di-my-ket-hon_du-lich-viet.jpg',
        ];
        $data = array_map(function ($card) use ($faker) {
            return [
                'name' => $faker->name,
                'info' => $faker->text(200),
                'images' => json_encode([$card]),
                'status' => array_rand(Identity::STATUS),
                'created_at' => \Carbon\Carbon::now(),
            ];
        }, $identityCards);

        Identity::insert($data);
    }
}
