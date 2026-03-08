<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /** 自分が行った勤怠情報が全て表示されている */
    public function test_all_attendance_records_are_displayed()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2026, 3, 1)->format('Y-m-d'),
            'clock_in' => Carbon::create(2026, 3, 1, 9, 0, 0),
            'clock_out' => Carbon::create(2026, 3, 1, 18, 0, 0),
            'status' => Attendance::STATUS_FINISHED,
        ]);
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2026, 3, 2)->format('Y-m-d'),
            'clock_in' => Carbon::create(2026, 3, 2, 10, 0, 0),
            'clock_out' => Carbon::create(2026, 3, 2, 19, 0, 0),
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-03');

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /** 勤怠一覧画面に遷移した際に現在の月が表示される */
    public function test_current_month_is_displayed()
    {
        $user = User::factory()->create();

        Carbon::setTestNow(Carbon::create(2026, 3, 8));

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('2026/03');

        Carbon::setTestNow();
    }

    /** 「前月」を押下した時に表示月の前月の情報が表示される */
    public function test_previous_month_is_displayed()
    {
        $user = User::factory()->create();

        Carbon::setTestNow(Carbon::create(2026, 3, 1));

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2026, 2, 15)->format('Y-m-d'),
            'clock_in' => Carbon::create(2026, 2, 15, 9, 0, 0),
            'clock_out' => Carbon::create(2026, 2, 15, 18, 0, 0),
            'status' => Attendance::STATUS_FINISHED,
        ]);

        // まず現在月（3月）にアクセス
        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertSee('2026/03');

        // 前月ボタンを押す → 2月に遷移
        $response = $this->actingAs($user)->get('/attendance/list?month=2026-02');
        $response->assertSee('2026/02');
        $response->assertSee('09:00');

        Carbon::setTestNow();
    }

    /** 「翌月」を押下した時に表示月の翌月の情報が表示される */
    public function test_next_month_is_displayed()
    {
        $user = User::factory()->create();

        Carbon::setTestNow(Carbon::create(2026, 3, 1));

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2026, 4, 10)->format('Y-m-d'),
            'clock_in' => Carbon::create(2026, 4, 10, 9, 0, 0),
            'clock_out' => Carbon::create(2026, 4, 10, 18, 0, 0),
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertSee('2026/03');

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-04');

        $response->assertStatus(200);
        $response->assertSee('2026/04');
        $response->assertSee('09:00');

        Carbon::setTestNow();
    }

    /** 「詳細」を押下すると、その日の勤怠詳細画面に遷移する */
    public function test_detail_link_navigates_to_detail_page()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2026, 3, 1)->format('Y-m-d'),
            'clock_in' => Carbon::create(2026, 3, 1, 9, 0, 0),
            'clock_out' => Carbon::create(2026, 3, 1, 18, 0, 0),
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-03');
        $response->assertSee(route('attendance.show', $attendance->id));

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);
    }


    }
