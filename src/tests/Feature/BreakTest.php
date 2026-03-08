<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    /** 休憩ボタンが正しく機能する */
    public function test_break_start_button_works()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => Carbon::now(),
            'status' => Attendance::STATUS_WORKING,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');

        $this->actingAs($user)->post('/attendance', [
            'action' => 'break_start',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩中');
    }

    /** 休憩は一日に何回でもできる */
    public function test_break_can_be_taken_multiple_times()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => Carbon::now(),
            'status' => Attendance::STATUS_WORKING,
        ]);

        $this->actingAs($user)->post('/attendance', [
            'action' => 'break_start',
        ]);
        $this->actingAs($user)->post('/attendance', [
            'action' => 'break_end',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');
    }

    /** 休憩戻ボタンが正しく機能する */
    public function test_break_end_button_works()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => Carbon::now(),
            'status' => Attendance::STATUS_WORKING,
        ]);

        $this->actingAs($user)->post('/attendance', [
            'action' => 'break_start',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');

        $this->actingAs($user)->post('/attendance', [
            'action' => 'break_end',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    /** 休憩戻は一日に何回でもできる */
    public function test_break_end_can_be_done_multiple_times()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => Carbon::now(),
            'status' => Attendance::STATUS_WORKING,
        ]);

        $this->actingAs($user)->post('/attendance', [
            'action' => 'break_start',
        ]);
        $this->actingAs($user)->post('/attendance', [
            'action' => 'break_end',
        ]);
        $this->actingAs($user)->post('/attendance', [
            'action' => 'break_start',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');
    }

    /** 休憩時刻が勤怠一覧画面で確認できる */
    public function test_break_time_is_displayed_on_list()
    {
        $user = User::factory()->create();

        Carbon::setTestNow(Carbon::create(2026, 3, 8, 9, 0, 0));

        $this->actingAs($user)->post('/attendance', [
            'action' => 'clock_in',
        ]);

        Carbon::setTestNow(Carbon::create(2026, 3, 8, 12, 0, 0));

        $this->actingAs($user)->post('/attendance', [
            'action' => 'break_start',
        ]);

        Carbon::setTestNow(Carbon::create(2026, 3, 8, 13, 0, 0));

        $this->actingAs($user)->post('/attendance', [
            'action' => 'break_end',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('1:00');

        Carbon::setTestNow();
    }
}
