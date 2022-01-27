<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('en_US');
        for ($i=0; $i < 500; $i++) {
            DB::table('users')->insert([
                'nickname' => $faker->userName(),
                'email' => $faker->email(),
                'email_verified_at' => $faker->dateTime($max = 'now', $timezone = null),
                'password' => Hash::make($faker->password),
                'profile' => $faker->randomElement(['private', 'professional', 'administrator']),
                'created_at' => $faker->dateTime($max = 'now', $timezone = null),
            ]);
        }
    }
}
