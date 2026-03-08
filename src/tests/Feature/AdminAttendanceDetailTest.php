<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminAndAttendance()
    {
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

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

        return [$admin, $attendance];
    }

    /** 勤怠詳細画面に表示されるデータが選択したものになっている */
    public function test_detail_displays_selected_attendance_data()
    {
        [$admin, $attendance] = $this->createAdminAndAttendance();

        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
        $response->assertSee('2026年');
        $response->assertSee('3月1日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    /** 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される */
    public function test_validation_error_when_clock_in_after_clock_out()
    {
        [$admin, $attendance] = $this->createAdminAndAttendance();

        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/' . $attendance->id);
        $response->assertStatus(200);

        $response = $this->actingAs($admin, 'admin')->post('/admin/attendance/' . $attendance->id, [
            'clock_in' => '19:00',
            'clock_out' => '18:00',
            'note' => '修正理由テスト',
        ]);

        $response->assertSessionHasErrors('clock_out');
        $this->assertStringContainsString(
            '出勤時間もしくは退勤時間が不適切な値です',
            session('errors')->first('clock_out')
        );
    }

    /** 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される */
    public function test_validation_error_when_rest_start_after_clock_out()
    {
        [$admin, $attendance] = $this->createAdminAndAttendance();

        $response = $this->actingAs($admin, 'admin')->post('/admin/attendance/' . $attendance->id, [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rests' => [
                ['start' => '19:00', 'end' => '20:00'],
            ],
            'note' => '修正理由テスト',
        ]);

        $response->assertSessionHasErrors('rests.0.start');
        $this->assertStringContainsString(
            '休憩時間が不適切な値です',
            session('errors')->first('rests.0.start')
        );
    }

    /** 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される */
    public function test_validation_error_when_rest_end_after_clock_out()
    {
        [$admin, $attendance] = $this->createAdminAndAttendance();

        $response = $this->actingAs($admin, 'admin')->post('/admin/attendance/' . $attendance->id, [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rests' => [
                ['start' => '17:00', 'end' => '19:00'],
            ],
            'note' => '修正理由テスト',
        ]);

        $response->assertSessionHasErrors('rests.0.end');
        $this->assertStringContainsString(
            '休憩時間もしくは退勤時間が不適切な値です',
            session('errors')->first('rests.0.end')
        );
    }

    /** 備考欄が未入力の場合のエラーメッセージが表示される */
    public function test_validation_error_when_note_is_empty()
    {
        [$admin, $attendance] = $this->createAdminAndAttendance();

        $response = $this->actingAs($admin, 'admin')->post('/admin/attendance/' . $attendance->id, [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'note' => '',
        ]);

        $response->assertSessionHasErrors('note');
        $this->assertStringContainsString(
            '備考を記入してください',
            session('errors')->first('note')
        );
    }
}
