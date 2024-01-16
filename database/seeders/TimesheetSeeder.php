<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\Timesheet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TimesheetSeeder extends Seeder
{
    private $startTime;
    private $endTime;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $start = $this->command->ask(
            'Ngày bắt đầu(Y-m-d):',
            Carbon::parse('-30 days')->format('Y-m-d')
        );
        $end = $this->command->ask(
            'Ngày kết thúc(Y-m-d):',
            Carbon::parse('now')->format('Y-m-d')
        );
        $userId = $this->command->ask(
            'Nhập id của user nếu không sẽ tạo tất cả user.',
            ''
        );
        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);
        if ($startDate->gt($endDate)) {
            return $this->command->comment('Dữ liệu đầu vào sai!');
        }

        if (empty($userId)) {
            $users = User::where('id', '<>', 1)->get()->pluck('id')->toArray();
        } else {
            $users = $userId;
        }
        $settings =  Setting::select('key', 'value')->pluck('value', 'key')->toArray();
        $checkInTime = $settings['check_in_time'];
        $flexibleTimeMinutes = $settings['flexible_time'] * 60;
        $this->startTime = Carbon::parse($checkInTime)->timestamp;
        $this->endTime = Carbon::parse($checkInTime)->addMinutes($flexibleTimeMinutes)->timestamp;

        while ($startDate->lte($endDate)) {
            if (!$startDate->isWeekend()) {
                $recordDate = $startDate->format('Y-m-d');
                if (is_array($users)) {
                    foreach ($users as $userId) {
                        $this->addRecord($recordDate, $userId, $settings);
                    }
                } else {
                    $this->addRecord($recordDate, $userId, $settings);
                }
            }

            $startDate->addDay();
        }
    }

    private function addRecord($recordDate, $userId, $settings)
    {
        if (
            Timesheet::where('record_date', $recordDate)
            ->where('user_id', $userId)->count() == 0
        ) {
            $user = User::find($userId);
            $time = Carbon::createFromTimestamp(mt_rand($this->startTime, $this->endTime));
            $in = $time->toTimeString();
            $time->addMinutes(rand(520, 600));
            $out = $time->toTimeString();
            $workingHours = calculator_working_hours($in, $out);
            $leaveHours = 0;
            $leaveHoursLeft = $user->leave_hours_left;
            $leaveHoursLeftInMonth = $user->leave_hours_left_in_month;
            if ($workingHours != (int) $settings['max_working_minutes_everyday_day']) {
                if ($leaveHoursLeft > 0) {
                    $leaveHours = rand(0, min($leaveHoursLeft * 4, 4)) / 4;
                    $leaveHoursLeft -= $leaveHours;
                    $user->leave_hours_left -= $leaveHours;
                    $user->save();
                } elseif ($leaveHoursLeftInMonth > 0) {
                    $leaveHours = rand(0, min($leaveHoursLeftInMonth * 4, 4) / 4);
                    $leaveHoursLeftInMonth -= $leaveHours;
                    $user->leave_hours_left_in_month -= $leaveHours;
                    $user->save();
                }
            }
            $totalHours = $workingHours + $leaveHours;
            if ($totalHours >= $settings['max_working_minutes_everyday_day']) {
                $status = 1;
            } else {
                $status = 2;
            }
            $rand = rand(0, 10);
            $otHours = 0;
            if ($rand > 9) {
                $otHours = rand(4, 10) / 4;
            }
            Timesheet::factory()->create([
                'user_id' => $userId,
                'record_date' => $recordDate,
                'in_time' => $in,
                'out_time' => $out,
                'check_in' => $in,
                'check_out' => $out,
                'status' => $status,
                'working_hours' => $workingHours * 60,
                'leave_hours' => $leaveHours * 60,
                'overtime_hours' => $otHours * 60,
            ]);
        }
    }
}
