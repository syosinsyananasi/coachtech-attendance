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

class AdminAttendanceCorrectionTest extends TestCase
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

    private function createCorrectionRequest($user, $date, $note)
    {
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in' => Carbon::parse($date . ' 09:00:00'),
            'clock_out' => Carbon::parse($date . ' 18:00:00'),
            'status' => Attendance::STATUS_FINISHED,
        ]);

        Rest::create([
            'attendance_id' => $attendance->id,
            'rest_start' => Carbon::parse($date . ' 12:00:00'),
            'rest_end' => Carbon::parse($date . ' 13:00:00'),
        ]);

        $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'rests' => [
                ['start' => '12:30', 'end' => '13:30'],
            ],
            'note' => $note,
        ]);

        return [$attendance, CorrectionRequest::where('attendance_id', $attendance->id)->first()];
    }

    /** 承認待ちの修正申請が全て表示されている */
    public function test_all_pending_requests_are_displayed()
    {
        $admin = $this->createAdmin();
        $user1 = User::factory()->create(['name' => 'ユーザーA']);
        $user2 = User::factory()->create(['name' => 'ユーザーB']);

        $this->createCorrectionRequest($user1, '2026-03-01', '申請理由A');
        $this->createCorrectionRequest($user2, '2026-03-02', '申請理由B');

        $response = $this->actingAs($admin, 'admin')->get('/stamp_correction_request/list?tab=pending');

        $response->assertStatus(200);
        $response->assertSee('ユーザーA');
        $response->assertSee('申請理由A');
        $response->assertSee('ユーザーB');
        $response->assertSee('申請理由B');
    }

    /** 承認済みの修正申請が全て表示されている */
    public function test_all_approved_requests_are_displayed()
    {
        $admin = $this->createAdmin();
        $user1 = User::factory()->create(['name' => 'ユーザーC']);
        $user2 = User::factory()->create(['name' => 'ユーザーD']);

        [, $correctionRequest1] = $this->createCorrectionRequest($user1, '2026-03-01', '承認済み理由C');
        [, $correctionRequest2] = $this->createCorrectionRequest($user2, '2026-03-02', '承認済み理由D');

        // 管理者が承認
        $this->actingAs($admin, 'admin')->post('/stamp_correction_request/approve/' . $correctionRequest1->id);
        $this->actingAs($admin, 'admin')->post('/stamp_correction_request/approve/' . $correctionRequest2->id);

        $response = $this->actingAs($admin, 'admin')->get('/stamp_correction_request/list?tab=approved');

        $response->assertStatus(200);
        $response->assertSee('ユーザーC');
        $response->assertSee('承認済み理由C');
        $response->assertSee('ユーザーD');
        $response->assertSee('承認済み理由D');
    }

    /** 修正申請の詳細内容が正しく表示されている */
    public function test_correction_request_detail_is_displayed_correctly()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create(['name' => '詳細ユーザー']);

        [, $correctionRequest] = $this->createCorrectionRequest($user, '2026-03-15', '詳細確認テスト');

        $response = $this->actingAs($admin, 'admin')->get('/stamp_correction_request/approve/' . $correctionRequest->id);

        $response->assertStatus(200);
        $response->assertSee('詳細ユーザー');
        $response->assertSee('2026年');
        $response->assertSee('3月15日');
    }

    /** 修正申請の承認処理が正しく行われる */
    public function test_approval_updates_attendance_record()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();

        [$attendance, $correctionRequest] = $this->createCorrectionRequest($user, '2026-03-01', '承認テスト');

        // 承認処理
        $response = $this->actingAs($admin, 'admin')->post('/stamp_correction_request/approve/' . $correctionRequest->id);
        $response->assertRedirect();

        // 修正申請が承認済みになっている
        $correctionRequest->refresh();
        $this->assertEquals(CorrectionRequest::STATUS_APPROVED, $correctionRequest->status);
        $this->assertNotNull($correctionRequest->approved_at);

        // 勤怠情報が更新されている
        $attendance->refresh();
        $this->assertEquals('10:00', $attendance->clock_in->format('H:i'));
        $this->assertEquals('19:00', $attendance->clock_out->format('H:i'));
    }
}
