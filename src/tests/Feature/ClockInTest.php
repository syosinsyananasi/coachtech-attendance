<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockInTest extends TestCase
{
    use RefreshDatabase;

    /** 出勤ボタンが正しく機能する */
    public function test_clock_in_button_works()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤');

        $this->actingAs($user)->post('/attendance', [
            'action' => 'clock_in',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    /** 出勤は一日一回のみできる */
    public function test_clock_in_button_not_displayed_when_finished()
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
        $response->assertDontSee('value="clock_in"', false);
    }

    /** 出勤時刻が勤怠一覧画面で確認できる */
    public function test_clock_in_time_is_displayed_on_list()
    {
        $user = User::factory()->create();

        Carbon::setTestNow(Carbon::create(2026, 3, 8, 9, 0, 0));

        $this->actingAs($user)->post('/attendance', [
            'action' => 'clock_in',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('09:00');

        Carbon::setTestNow();
    }
}
