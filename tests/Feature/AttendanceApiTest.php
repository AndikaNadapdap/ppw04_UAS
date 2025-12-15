<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceApiTest extends TestCase
{
    use RefreshDatabase; // Reset database setiap kali test jalan

    // Test Login
    public function test_user_can_login_and_get_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['access_token']);
    }

    // Test Check-in (Positive Case)
    public function test_user_can_check_in()
    {
        $user = User::factory()->create();

        // actingAs($user) otomatis login dan pakai token
        $response = $this->actingAs($user)->postJson('/api/attendance/in');

        $response->assertStatus(201)
                 ->assertJson(['message' => 'Check-in berhasil']);
        
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'check_out' => null
        ]);
    }

    // Test Double Check-in (Negative Case)
    public function test_user_cannot_double_check_in()
    {
        $user = User::factory()->create();

        // Check-in pertama
        $this->actingAs($user)->postJson('/api/attendance/in');

        // Check-in kedua (harus gagal)
        $response = $this->actingAs($user)->postJson('/api/attendance/in');

        $response->assertStatus(400)
                 ->assertJsonFragment(['message' => 'Anda masih status Check-in. Harap Check-out dulu.']);
    }

    // Test Check-out
    public function test_user_can_check_out()
    {
        $user = User::factory()->create();

        // Check-in dulu
        $this->actingAs($user)->postJson('/api/attendance/in');

        // Lalu Check-out
        $response = $this->actingAs($user)->postJson('/api/attendance/out');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Check-out berhasil']);
    }
}
