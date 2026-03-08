<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /** メールアドレスが未入力の場合、バリデーションメッセージが表示される */
    public function test_email_is_required()
    {
        Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /** パスワードが未入力の場合、バリデーションメッセージが表示される */
    public function test_password_is_required()
    {
        Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /** 登録内容と一致しない場合、バリデーションメッセージが表示される */
    public function test_login_fails_with_wrong_credentials()
    {
        Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }
}
