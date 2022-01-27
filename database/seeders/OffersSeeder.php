<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class OffersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('en_US');
        for ($i=0; $i < 300; $i++) {
            DB::table('offers')->insert([
                'cardId' => $faker->numberBetween($min = 1, $max = 808),
                'userId' => $faker->numberBetween($min = 1, $max = 500),
                'quantity' => $faker->numberBetween($min = 1, $max = 10),
                'price' => $faker->randomFloat($nbMaxDecimals = 2, $min = 1, $max = 500),
                'created_at' => $faker->dateTime($max = 'now', $timezone = null),
            ]);
        }
    }
}
