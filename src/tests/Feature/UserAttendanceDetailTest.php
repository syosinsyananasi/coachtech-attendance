<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use App\Models\Rest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /** 勤怠詳細画面の「名前」がログインユーザーの氏名になっている */
    public function test_name_matches_logged_in_user()
    {
        $user = User::factory()->create(['name' => 'テスト太郎']);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2026, 3, 1)->format('Y-m-d'),
            'clock_in' => Carbon::create(2026, 3, 1, 9, 0, 0),
            'clock_out' => Carbon::create(2026, 3, 1, 18, 0, 0),
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
    }

    /** 勤怠詳細画面の「日付」が選択した日付になっている */
    public function test_date_matches_selected_date()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2026, 3, 15)->format('Y-m-d'),
            'clock_in' => Carbon::create(2026, 3, 15, 9, 0, 0),
            'clock_out' => Carbon::create(2026, 3, 15, 18, 0, 0),
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('2026');
        $response->assertSee('3月15日');
    }

    /** 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している */
    public function test_clock_in_and_clock_out_match_user_records()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2026, 3, 1)->format('Y-m-d'),
            'clock_in' => Carbon::create(2026, 3, 1, 9, 30, 0),
            'clock_out' => Carbon::create(2026, 3, 1, 18, 45, 0),
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('09:30');
        $response->assertSee('18:45');
    }

    /** 「休憩」にて記されている時間がログインユーザーの打刻と一致している */
    public function test_rest_times_match_user_records()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2026, 3, 1)->format('Y-m-d'),
            'clock_in' => Carbon::create(2026, 3, 1, 9, 0, 0),
            'clock_out' => Carbon::create(2026, 3, 1, 18, 0, 0),
            'status' => Attendance::STATUS_FINISHED,
        ]);

        Rest::create([
            'attendance_id' => $attendance->id,
            'rest_start' => Carbon::create(2026, 3, 1, 12, 0, 0),
            'rest_end' => Carbon::create(2026, 3, 1, 13, 0, 0),
        ]);

        Rest::create([
            'attendance_id' => $attendance->id,
            'rest_start' => Carbon::create(2026, 3, 1, 15, 30, 0),
            'rest_end' => Carbon::create(2026, 3, 1, 15, 45, 0),
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('15:30');
        $response->assertSee('15:45');
    }
}
