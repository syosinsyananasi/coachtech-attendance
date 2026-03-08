<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DateTimeTest extends TestCase
{
    use RefreshDatabase;

    /** 現在の日時情報がUIと同じ形式で出力されている */
    public function test_current_datetime_is_displayed_on_stamp_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('id="current-date"', false);
        $response->assertSee('id="current-time"', false);
        $response->assertSee('attendance-clock.js', false);
    }
}
