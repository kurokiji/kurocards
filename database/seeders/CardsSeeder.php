<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class CardsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('en_US');
        for ($i=0; $i < 1000; $i++) {
            DB::table('cards')->insert([
                'name' => $faker->numerify('Card ###'),
                'description' => $faker->sentence($nbWords = 8, $variableNbWords = true),
                'created_at' => $faker->dateTime($max = 'now', $timezone = null),
            ]);
        }
    }
}
