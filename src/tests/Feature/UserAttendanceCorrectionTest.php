<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithAttendance()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2026, 3, 1)->format('Y-m-d'),
            'clock_in' => Carbon::create(2026, 3, 1, 9, 0, 0),
            'clock_out' => Carbon::create(2026, 3, 1, 18, 0, 0),
            'status' => Attendance::STATUS_FINISHED,
        ]);

        return [$user, $attendance];
    }

    /** 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される */
    public function test_validation_error_when_clock_in_after_clock_out()
    {
        [$user, $attendance] = $this->createUserWithAttendance();

        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
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
        [$user, $attendance] = $this->createUserWithAttendance();

        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
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
        [$user, $attendance] = $this->createUserWithAttendance();

        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
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
        [$user, $attendance] = $this->createUserWithAttendance();

        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
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

    /** 修正申請処理が実行される（管理者の承認画面と申請一覧画面に表示される） */
    public function test_correction_request_appears_in_admin_views()
    {
        $user = User::factory()->create(['name' => '申請太郎']);
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2026, 3, 1)->format('Y-m-d'),
            'clock_in' => Carbon::create(2026, 3, 1, 9, 0, 0),
            'clock_out' => Carbon::create(2026, 3, 1, 18, 0, 0),
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'note' => '修正理由テスト',
        ]);

        $correctionRequest = CorrectionRequest::first();
        $this->assertNotNull($correctionRequest);
        $this->assertEquals(CorrectionRequest::STATUS_PENDING, $correctionRequest->status);

        // 管理者の申請一覧画面に表示される
        $response = $this->actingAs($admin, 'admin')->get('/stamp_correction_request/list');
        $response->assertStatus(200);
        $response->assertSee('申請太郎');
        $response->assertSee('修正理由テスト');

        // 管理者の承認画面に表示される
        $response = $this->actingAs($admin, 'admin')->get('/stamp_correction_request/approve/' . $correctionRequest->id);
        $response->assertStatus(200);
        $response->assertSee('申請太郎');
        $response->assertSee('2026');
        $response->assertSee('3月1日');
    }

    /** 「承認待ち」にログインユーザーが行った申請が全て表示されていること */
    public function test_all_pending_requests_displayed_in_list()
    {
        $user = User::factory()->create();

        $attendance1 = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2026, 3, 1)->format('Y-m-d'),
            'clock_in' => Carbon::create(2026, 3, 1, 9, 0, 0),
            'clock_out' => Carbon::create(2026, 3, 1, 18, 0, 0),
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2026, 3, 2)->format('Y-m-d'),
            'clock_in' => Carbon::create(2026, 3, 2, 9, 0, 0),
            'clock_out' => Carbon::create(2026, 3, 2, 18, 0, 0),
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $this->actingAs($user)->post('/attendance/detail/' . $attendance1->id, [
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'note' => '修正理由1',
        ]);

        $this->actingAs($user)->post('/attendance/detail/' . $attendance2->id, [
            'clock_in' => '11:00',
            'clock_out' => '20:00',
            'note' => '修正理由2',
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee('修正理由1');
        $response->assertSee('修正理由2');
    }

    /** 「承認済み」に管理者が承認した修正申請が全て表示されている */
    public function test_approved_requests_displayed_in_approved_tab()
    {
        $user = User::factory()->create();
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2026, 3, 1)->format('Y-m-d'),
            'clock_in' => Carbon::create(2026, 3, 1, 9, 0, 0),
            'clock_out' => Carbon::create(2026, 3, 1, 18, 0, 0),
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'note' => '承認テスト',
        ]);

        $correctionRequest = CorrectionRequest::first();

        // 管理者が承認
        $this->actingAs($admin, 'admin')->post('/stamp_correction_request/approve/' . $correctionRequest->id);

        // ユーザーの承認済みタブに表示される
        $response = $this->actingAs($user)->get('/stamp_correction_request/list?tab=approved');

        $response->assertStatus(200);
        $response->assertSee('承認テスト');
        $response->assertSee('承認済み');
    }

    /** 各申請の「詳細」を押下すると勤怠詳細画面に遷移する */
    public function test_detail_link_navigates_to_attendance_detail()
    {
        [$user, $attendance] = $this->createUserWithAttendance();

        $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'note' => '詳細遷移テスト',
        ]);

        // 申請一覧に詳細リンクが表示される
        $response = $this->actingAs($user)->get('/stamp_correction_request/list');
        $response->assertStatus(200);
        $response->assertSee(route('attendance.show', $attendance->id));

        // 勤怠詳細画面に遷移できる
        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);
    }
}
