<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{

    private string $defaultPassword = "P@ssw0rd!123";
    private int $maxUser = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $total_user = User::where("id", "<>", 0)->count();
        $max = $this->maxUser;

        if($total_user == 0)
        {
            for($i = 1; $i <= $max; $i++)
            {
                $faker = Faker::create();
                $gender = rand(1,2);
                $first_name = $gender == 1 ? $faker->firstNameMale : $faker->firstNameFemale;
                User::create([
                    'email'             => $faker->safeEmail,
                    'phone'             => $faker->phoneNumber,
                    'password'          => Hash::make($this->defaultPassword),
                    'first_name'        => $first_name,
                    'last_name'         => $faker->lastName,
                    'gender'            => $gender == 1 ? "M" : "F",
                    'country'           => $faker->country,
                    'facebook'          => $faker->username,
                    'instagram'         => $faker->username,
                    'twitter'           => $faker->username,
                    'linked_in'         => $faker->username,
                    'address'           => $faker->streetAddress,
                    'about_me'          => $faker->text,
                    'confirmed'         => 1,
                    'remember_token'    => $faker->uuid,
                ]);
            }
        }

    }
}
