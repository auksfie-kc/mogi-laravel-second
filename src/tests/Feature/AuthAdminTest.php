<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;

class AuthAdminTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function admin_can_login_with_correct_credentials()
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpass'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'adminpass',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/attendance/list');
        $this->assertAuthenticatedAs($admin, 'admin'); // adminガードを使用
    }

    /** @test */
    public function admin_cannot_login_with_invalid_password()
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpass'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors();
        $this->assertGuest('admin'); // ログイン失敗を確認
    }
}
