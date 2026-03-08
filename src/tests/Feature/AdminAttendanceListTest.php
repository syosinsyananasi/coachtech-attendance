<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin()
    {
        return Admin::create([
            'name' => '管理者',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
    }

    /** その日になされた全ユーザーの勤怠情報が正確に確認できる */
    public function test_all_users_attendance_displayed_accurately()
    {
        $admin = $this->createAdmin();

        $user1 = User::factory()->create(['name' => 'ユーザーA']);
        $user2 = User::factory()->create(['name' => 'ユーザーB']);

        Attendance::create([
            'user_id' => $user1->id,
            'date' => '2026-03-08',
            'clock_in' => Carbon::create(2026, 3, 8, 9, 0, 0),
            'clock_out' => Carbon::create(2026, 3, 8, 18, 0, 0),
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'date' => '2026-03-08',
            'clock_in' => Carbon::create(2026, 3, 8, 10, 0, 0),
            'clock_out' => Carbon::create(2026, 3, 8, 19, 0, 0),
            'status' => Attendance::STATUS_FINISHED,
        ]);

        Rest::create([
            'attendance_id' => $attendance2->id,
            'rest_start' => Carbon::create(2026, 3, 8, 12, 0, 0),
            'rest_end' => Carbon::create(2026, 3, 8, 13, 0, 0),
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list?date=2026-03-08');

        $response->assertStatus(200);
        $response->assertSee('ユーザーA');
        $response->assertSee('ユーザーB');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('1:00');
    }

    /** 遷移した際に現在の日付が表示される */
    public function test_current_date_is_displayed()
    {
        $admin = $this->createAdmin();

        Carbon::setTestNow(Carbon::create(2026, 3, 8));

        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('2026年3月8日');

        Carbon::setTestNow();
    }

    /** 「前日」を押下した時に前の日の勤怠情報が表示される */
    public function test_previous_day_attendance_is_displayed()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create(['name' => '前日ユーザー']);

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-07',
            'clock_in' => Carbon::create(2026, 3, 7, 9, 30, 0),
            'clock_out' => Carbon::create(2026, 3, 7, 17, 30, 0),
            'status' => Attendance::STATUS_FINISHED,
        ]);

        // 当日（3/8）にアクセス
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list?date=2026-03-08');
        $response->assertSee('2026年3月8日');

        // 前日ボタンを押す → 3/7に遷移
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list?date=2026-03-07');
        $response->assertStatus(200);
        $response->assertSee('2026年3月7日');
        $response->assertSee('前日ユーザー');
        $response->assertSee('09:30');
        $response->assertSee('17:30');
    }

    /** 「翌日」を押下した時に次の日の勤怠情報が表示される */
    public function test_next_day_attendance_is_displayed()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create(['name' => '翌日ユーザー']);

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-09',
            'clock_in' => Carbon::create(2026, 3, 9, 8, 0, 0),
            'clock_out' => Carbon::create(2026, 3, 9, 16, 0, 0),
            'status' => Attendance::STATUS_FINISHED,
        ]);

        // 当日（3/8）にアクセス
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list?date=2026-03-08');
        $response->assertSee('2026年3月8日');

        // 翌日ボタンを押す → 3/9に遷移
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list?date=2026-03-09');
        $response->assertStatus(200);
        $response->assertSee('2026年3月9日');
        $response->assertSee('翌日ユーザー');
        $response->assertSee('08:00');
        $response->assertSee('16:00');
    }
}
