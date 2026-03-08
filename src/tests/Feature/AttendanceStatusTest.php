<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    /** 勤務外の場合、勤怠ステータスが正しく表示される */
    public function test_status_off_is_displayed()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    /** 出勤中の場合、勤怠ステータスが正しく表示される */
    public function test_status_working_is_displayed()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => Carbon::now(),
            'status' => Attendance::STATUS_WORKING,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    /** 休憩中の場合、勤怠ステータスが正しく表示される */
    public function test_status_on_break_is_displayed()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => Carbon::now(),
            'status' => Attendance::STATUS_ON_BREAK,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    /** 退勤済の場合、勤怠ステータスが正しく表示される */
    public function test_status_finished_is_displayed()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}
