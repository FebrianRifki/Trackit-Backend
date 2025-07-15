<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_can_login(): void
    {
        // Buat user manual
        $user = User::create([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'status',
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at'
                ]
            ]);

        // Pastikan cookie sanctum_token dibuat
        $this->assertNotNull($response->headers->getCookie('sanctum_token'));
    }

    public function test_user_can_access_protected_route(): void
    {
        // Buat user dan login
        $user = User::create([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $loginResponse->assertStatus(200);

        // Dapatkan cookie dari response login
        $cookie = $loginResponse->headers->getCookie('sanctum_token');

        // Gunakan cookie untuk request berikutnya
        $response = $this->withHeaders([
            'X-XSRF-TOKEN' => $this->getCsrfToken(),
        ])->withCookie($cookie)->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'created_at',
                'updated_at'
            ]);
    }

    public function test_user_can_logout(): void
    {
        // Buat user dan login
        $user = User::create([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $loginResponse->assertStatus(200);

        // Dapatkan cookie dari response login
        $cookie = $loginResponse->headers->getCookie('sanctum_token');

        // Logout
        $response = $this->withHeaders([
            'X-XSRF-TOKEN' => $this->getCsrfToken(),
        ])->withCookie($cookie)->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 200,
                'message' => 'Logout successful'
            ]);

        // Pastikan cookie sudah dihapus
        $this->assertNull($response->headers->getCookie('sanctum_token'));
    }
}
