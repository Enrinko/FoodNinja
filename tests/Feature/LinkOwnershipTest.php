<?php

namespace Tests\Feature;

use App\Filament\Resources\LinkResource;
use App\Models\Link;
use App\Models\User;
use App\Policies\LinkPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_link_resource_query_is_scoped_to_the_authenticated_user(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();
        Link::factory()->for($me)->create();
        Link::factory()->for($other)->create();

        $this->actingAs($me);

        $userIds = LinkResource::getEloquentQuery()->pluck('user_id')->unique()->all();

        $this->assertSame([$me->id], $userIds);
    }

    public function test_policy_forbids_acting_on_another_users_link(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $link = Link::factory()->for($owner)->create();

        $policy = new LinkPolicy();

        $this->assertTrue($policy->view($owner, $link));
        $this->assertTrue($policy->delete($owner, $link));
        $this->assertFalse($policy->view($stranger, $link));
        $this->assertFalse($policy->delete($stranger, $link));
    }
}
