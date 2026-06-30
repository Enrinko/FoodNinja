<?php

namespace Tests\Feature;

use App\Filament\Resources\LinkResource\Pages\CreateLink;
use App\Filament\Resources\LinkResource\Pages\ListLinks;
use App\Models\Link;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LinkPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_user_can_create_a_link_through_the_panel(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(CreateLink::class)
            ->fillForm(['original_url' => 'https://example.com/page'])
            ->call('create')
            ->assertHasNoFormErrors();

        $link = Link::where('user_id', $user->id)->first();

        $this->assertNotNull($link);
        $this->assertSame('https://example.com/page', $link->original_url);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{6}$/', $link->short_code);
    }

    public function test_list_shows_only_the_current_users_links(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();
        $mine = Link::factory()->for($me)->create();
        $theirs = Link::factory()->for($other)->create();

        $this->actingAs($me);

        Livewire::test(ListLinks::class)
            ->assertCanSeeTableRecords([$mine])
            ->assertCanNotSeeTableRecords([$theirs]);
    }
}
