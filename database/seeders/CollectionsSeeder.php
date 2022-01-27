<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class CollectionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('en_US');
        for ($i=0; $i < 100; $i++) {
            DB::table('collections')->insert([
                'name' => $faker->numerify('Collection ###'),
                'image' => $faker->imageUrl($width = 50, $height = 50),
                'edition' => $faker->dateTime($max = 'now', $timezone = null),
                'created_at' => $faker->dateTime($max = 'now', $timezone = null),
            ]);
        }
    }
}
