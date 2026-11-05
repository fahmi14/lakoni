<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LakoniAuthAndUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_page_is_accessible()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('LAKONI');
    }

    public function test_admin_can_login_successfully()
    {
        $response = $this->post('/login', [
            'email' => 'admin@lakoni.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_petugas_can_login_successfully()
    {
        $response = $this->post('/login', [
            'email' => 'petugas@lakoni.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_user_cannot_login_with_incorrect_password()
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'admin@lakoni.id',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_forgot_password_page_is_accessible()
    {
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
        $response->assertSee('Reset Password');
    }

    public function test_forgot_password_can_send_reset_link()
    {
        $response = $this->post('/forgot-password', [
            'email' => 'admin@lakoni.id',
        ]);

        $response->assertSessionHas('status');
    }

    public function test_petugas_cannot_access_user_management()
    {
        $petugas = User::where('role', 'petugas')->first();

        $response = $this->actingAs($petugas)->get('/users');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_user_management()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/users');
        $response->assertStatus(200);
        $response->assertSee('Manajemen Pengguna');
    }

    public function test_admin_can_create_new_user()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Petugas Baru',
            'email' => 'petugasbaru@lakoni.id',
            'password' => 'password123',
            'role' => 'petugas',
        ]);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'email' => 'petugasbaru@lakoni.id',
            'role' => 'petugas',
        ]);
    }

    public function test_admin_can_update_user()
    {
        $admin = User::where('role', 'admin')->first();
        $petugas = User::where('role', 'petugas')->first();

        $response = $this->actingAs($admin)->put("/users/{$petugas->id}", [
            'name' => 'Petugas Terupdate',
            'email' => 'petugas.updated@lakoni.id',
            'role' => 'petugas',
        ]);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'id' => $petugas->id,
            'name' => 'Petugas Terupdate',
            'email' => 'petugas.updated@lakoni.id',
        ]);
    }

    public function test_admin_can_delete_petugas()
    {
        $admin = User::where('role', 'admin')->first();
        $petugas = User::where('role', 'petugas')->first();

        $response = $this->actingAs($admin)->delete("/users/{$petugas->id}");

        $response->assertRedirect('/users');
        $this->assertDatabaseMissing('users', [
            'id' => $petugas->id,
        ]);
    }

    public function test_admin_cannot_delete_themselves()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->delete("/users/{$admin->id}");

        $response->assertRedirect('/users');
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }
}

