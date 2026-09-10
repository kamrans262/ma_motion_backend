<?php

namespace Tests\Feature\Admin;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Content\Models\AppContentPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminContentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_search_edit_publish_and_soft_delete_optional_content(): void
    {
        $admin = User::factory()->create(['role'=>UserRole::Admin,'status'=>UserStatus::Active]);

        $this->actingAs($admin)->post('/admin/content', ['title'=>'About MA Motion','slug'=>'about-ma-motion','body'=>'About body','sort_order'=>100])->assertRedirect();
        $page = AppContentPage::query()->where('slug','about-ma-motion')->firstOrFail();
        $this->actingAs($admin)->get('/admin/content?search=About')->assertOk()->assertSee('About MA Motion');
        $this->actingAs($admin)->put('/admin/content/'.$page->id, ['title'=>'About MA Motion Updated','slug'=>'about-ma-motion','body'=>'Updated body','sort_order'=>90])->assertRedirect();
        $this->actingAs($admin)->patch('/admin/content/'.$page->id.'/publish')->assertRedirect();
        $this->assertTrue($page->fresh()->is_published);
        $this->actingAs($admin)->delete('/admin/content/'.$page->id)->assertRedirect('/admin/content');
        $this->assertSoftDeleted('app_content_pages', ['id'=>$page->id]);
    }

    public function test_required_legal_page_cannot_be_deleted_or_have_slug_changed(): void
    {
        $admin = User::factory()->create(['role'=>UserRole::Admin,'status'=>UserStatus::Active]);
        $page = AppContentPage::query()->create(['title'=>'Privacy Policy','slug'=>'privacy-policy','body'=>'Policy','is_system'=>true,'is_published'=>false,'sort_order'=>20]);

        $this->actingAs($admin)->put('/admin/content/'.$page->id, ['title'=>'Privacy Policy','slug'=>'changed-policy','body'=>'Updated policy','sort_order'=>20])->assertRedirect();
        $this->assertSame('privacy-policy', $page->fresh()->slug);
        $this->actingAs($admin)->from('/admin/content/'.$page->id.'/edit')->delete('/admin/content/'.$page->id)->assertRedirect('/admin/content/'.$page->id.'/edit')->assertSessionHasErrors('content');
        $this->assertDatabaseHas('app_content_pages', ['id'=>$page->id,'deleted_at'=>null]);
    }
}
