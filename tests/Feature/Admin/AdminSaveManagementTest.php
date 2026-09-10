<?php

namespace Tests\Feature\Admin;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Saves\Models\MakerSave;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSaveManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_inspect_search_and_filter_save_relationships_and_top_makers(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);
        $firstMaker = User::factory()->create(['name' => 'Most Hearted Maker', 'email' => 'topmaker@example.com', 'role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $secondMaker = User::factory()->create(['name' => 'Other Maker', 'email' => 'othermaker@example.com', 'role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $firstSaver = User::factory()->create(['name' => 'First Appreciator', 'email' => 'firstsave@example.com', 'role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        $secondSaver = User::factory()->create(['name' => 'Second Appreciator', 'email' => 'secondsave@example.com', 'role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        MakerSave::query()->create(['appreciator_id' => $firstSaver->id, 'maker_id' => $firstMaker->id]);
        MakerSave::query()->create(['appreciator_id' => $secondSaver->id, 'maker_id' => $firstMaker->id]);
        MakerSave::query()->create(['appreciator_id' => $firstSaver->id, 'maker_id' => $secondMaker->id]);

        $this->actingAs($admin)->get('/admin/saves')
            ->assertOk()
            ->assertSee('Hearts & Saved Makers', false)
            ->assertSee('Most Hearted Maker')
            ->assertSee('First Appreciator')
            ->assertSee('3 total saves')
            ->assertSeeInOrder(['Most Hearted Maker', '2 saves']);

        $this->actingAs($admin)->get('/admin/saves?search=secondsave%40example.com')
            ->assertOk()
            ->assertSee('Second Appreciator')
            ->assertSee('secondsave@example.com')
            ->assertDontSee('firstsave@example.com');

        $this->actingAs($admin)->get('/admin/saves?maker_id='.$secondMaker->id)
            ->assertOk()
            ->assertSee('Maker filter active')
            ->assertSee('Other Maker')
            ->assertDontSee('Second Appreciator');
    }
}
