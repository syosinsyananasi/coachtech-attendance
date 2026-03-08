<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStaffListTest extends TestCase
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

    /** 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる */
    public function test_all_staff_names_and_emails_are_displayed()
    {
        $admin = $this->createAdmin();

        $user1 = User::factory()->create(['name' => 'スタッフA', 'email' => 'staffa@test.com']);
        $user2 = User::factory()->create(['name' => 'スタッフB', 'email' => 'staffb@test.com']);

        $response = $this->actingAs($admin, 'admin')->get('/admin/staff/list');

        $response->assertStatus(200);
        $response->assertSee('スタッフA');
        $response->assertSee('staffa@test.com');
        $response->assertSee('スタッフB');
        $response->assertSee('staffb@test.com');
    }

    /** ユーザーの勤怠情報が正しく表示される */
    public function test_staff_attendance_is_displayed_accurately()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create(['name' => 'テスト太郎']);

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

        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/staff/' . $user->id . '?month=2026-03');

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('1:00');
        $response->assertSee('8:00');
    }

    /** 「前月」を押下した時に表示月の前月の情報が表示される */
    public function test_previous_month_is_displayed()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2026, 2, 15)->format('Y-m-d'),
            'clock_in' => Carbon::create(2026, 2, 15, 9, 0, 0),
            'clock_out' => Carbon::create(2026, 2, 15, 18, 0, 0),
            'status' => Attendance::STATUS_FINISHED,
        ]);

        // 3月にアクセス
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/staff/' . $user->id . '?month=2026-03');
        $response->assertSee('2026/03');

        // 前月（2月）に遷移
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/staff/' . $user->id . '?month=2026-02');
        $response->assertStatus(200);
        $response->assertSee('2026/02');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** 「翌月」を押下した時に表示月の翌月の情報が表示される */
    public function test_next_month_is_displayed()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2026, 4, 10)->format('Y-m-d'),
            'clock_in' => Carbon::create(2026, 4, 10, 10, 0, 0),
            'clock_out' => Carbon::create(2026, 4, 10, 19, 0, 0),
            'status' => Attendance::STATUS_FINISHED,
        ]);

        // 3月にアクセス
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/staff/' . $user->id . '?month=2026-03');
        $response->assertSee('2026/03');

        // 翌月（4月）に遷移
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/staff/' . $user->id . '?month=2026-04');
        $response->assertStatus(200);
        $response->assertSee('2026/04');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /** 「詳細」を押下するとその日の勤怠詳細画面に遷移する */
    public function test_detail_link_navigates_to_attendance_detail()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2026, 3, 1)->format('Y-m-d'),
            'clock_in' => Carbon::create(2026, 3, 1, 9, 0, 0),
            'clock_out' => Carbon::create(2026, 3, 1, 18, 0, 0),
            'status' => Attendance::STATUS_FINISHED,
        ]);

        // スタッフ勤怠一覧に詳細リンクが表示される
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/staff/' . $user->id . '?month=2026-03');
        $response->assertStatus(200);
        $response->assertSee(route('admin.attendance.show', $attendance->id));

        // 勤怠詳細画面に遷移できる
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/' . $attendance->id);
        $response->assertStatus(200);
    }
}
