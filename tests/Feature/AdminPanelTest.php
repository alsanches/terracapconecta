<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_active_admin_can_open_every_administrative_module(): void
    {
        $admin = User::query()->firstOrFail();

        foreach (['/admin', '/admin/lots', '/admin/lots/create', '/admin/notices', '/admin/data-sources', '/admin/sync-runs', '/admin/administrative-regions'] as $uri) {
            $this->actingAs($admin)->get($uri)->assertOk();
        }
    }

    public function test_inactive_user_cannot_access_panel(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'active' => false]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }
}
