<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForcePasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_must_change_password_is_redirected_to_change_password_page(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
            'password' => Hash::make('Admin@123'),
            'must_change_password' => true,
        ]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertRedirect('/admin/change-password');
    }

    public function test_user_without_must_change_password_can_access_admin(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
            'password' => Hash::make('Secret@123'),
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
    }
}
