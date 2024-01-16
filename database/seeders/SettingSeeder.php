<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::truncate();
        Setting::upsert(
            [
                ['key' => 'start_working', 'value' => '7:00'],
                ['key' => 'end_working', 'value' => '18:30'],
                ['key' => 'start_lunch', 'value' => '11:30'],
                ['key' => 'end_lunch', 'value' => '13:00'],
                ['key' => 'max_working_minutes_everyday_day', 'value' => '480'],
                ['key' => 'minute_block', 'value' => '60'],
            ],
            ['key'],
            ['value']
        );
    }
}