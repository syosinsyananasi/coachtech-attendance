<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    /** 退勤ボタンが正しく機能する */
    public function test_clock_out_button_works()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => Carbon::now(),
            'status' => Attendance::STATUS_WORKING,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤');

        $this->actingAs($user)->post('/attendance', [
            'action' => 'clock_out',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤済');
    }

    /** 退勤時刻が勤怠一覧画面で確認できる */
    public function test_clock_out_time_is_displayed_on_list()
    {
        $user = User::factory()->create();

        Carbon::setTestNow(Carbon::create(2026, 3, 8, 9, 0, 0));

        $this->actingAs($user)->post('/attendance', [
            'action' => 'clock_in',
        ]);

        Carbon::setTestNow(Carbon::create(2026, 3, 8, 18, 0, 0));

        $this->actingAs($user)->post('/attendance', [
            'action' => 'clock_out',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('18:00');

        Carbon::setTestNow();
    }
}
