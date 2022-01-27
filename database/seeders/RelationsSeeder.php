<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class RelationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('en_US');
        for ($i=0; $i < 808; $i++) {
            DB::table('relations')->insert([
                'cardId' => $i + 1,
                'collectionId' => $faker->numberBetween($min = 1, $max = 100),
                'created_at' => $faker->dateTime($max = 'now', $timezone = null),
            ]);
        }
    }
}
