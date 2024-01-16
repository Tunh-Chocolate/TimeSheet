<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Team;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => null,
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'), // password
            'remember_token' => Str::random(10),
            'code' => function ($attributes) {
                $name = $attributes['name'];
                $cleanedName = preg_replace('/[^A-Za-z0-9]/', '', $name);
                $code = strtolower($cleanedName);
                return $code;
            },
            'start_date' => Carbon::parse('- ' . rand(60, 1000) . ' days')->format(config('define.date_search')),
            'offical_start_date' => function ($attributes) {
                return Carbon::parse($attributes['start_date'])->addMonths(2)->format(config('define.date_search'));
            },
            'dependent_person' => 0,
            'gender' => rand(1, 2),
            'contract' => 1,
            'birthday' => Carbon::parse('-' . rand(7300, 21900) . ' days')->format(config('define.date_search')),
            'leave_hours_left' => rand(1, 32),
            'leave_hours_left_in_month' => function ($attributes) {
                return $attributes['gender'] == 2 ? 4 : 0;
            },
            'phone' => '',
            'status' => 1,
            'position' => 1,
            'user_id' => null,
            'avatar' => null,
            'role_id' => 2,
            'team_id' => rand(1, 2),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (User $user) {
            $user->roles()->sync([2]);
            $user->teams()->sync($user->team_id);
        });
    }
    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
