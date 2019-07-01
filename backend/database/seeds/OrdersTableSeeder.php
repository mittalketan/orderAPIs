<?php

use Illuminate\Database\Seeder;
use App\Models\Order;

class OrdersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();

        for ($i = 0; $i < 5; $i++) {

            $lat1     = $faker->latitude();
            $lat2     = $faker->latitude();
            $lon1     = $faker->longitude();
            $lon2     = $faker->longitude();
            $distance = $this->CalDistance($lat1, $lon1, $lat2, $lon2);

            DB::table('orders')->insert([

                'start_latitude'   => $lat1,
                'start_longtitude' => $lon1,
                'end_latitude'     => $lat2,
                'end_longtitude'   => $lon2,
                'distance'         => $distance,
                'status'           => $i % 2 == 0 ? Order::UNASSIGNED_STATUS : Order::ASSIGNED_STATUS,
                'created_at'       => date("Y-m-d H:i:s"),
                'updated_at'       => date("Y-m-d H:i:s"),
            ]);
        }
    }
    public function CalDistance($lat1, $lon1, $lat2, $lon2)
    {
        $theta           = $lon1 - $lon2;
        $dist            = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist            = acos($dist);
        $dist            = rad2deg($dist);
        $distanceInMetre = $dist * 60 * 1.1515 * 1.609344 * 1000;

        return $distanceInMetre;
    }

}
