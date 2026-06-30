<?php

namespace Tests\Feature;

use App\Models\Click;
use App\Models\Link;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrlShortenerTest extends TestCase
{
    use RefreshDatabase;

    public function test_visiting_a_short_link_redirects_to_the_original_url(): void
    {
        $link = Link::factory()->create([
            'original_url' => 'https://example.com/page',
        ]);

        $response = $this->get('/'.$link->short_code);

        $response->assertRedirect('https://example.com/page');
    }

    public function test_visiting_a_short_link_records_a_click(): void
    {
        $link = Link::factory()->create();

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.7'])
            ->get('/'.$link->short_code);

        $this->assertDatabaseCount('clicks', 1);

        $click = Click::first();
        $this->assertSame($link->id, $click->link_id);
        $this->assertSame('203.0.113.7', $click->ip_address);
        $this->assertNotNull($click->created_at);
    }

    public function test_an_unknown_short_code_returns_404(): void
    {
        $this->get('/zzzzzz')->assertNotFound();
    }

    public function test_a_short_code_is_generated_and_unique_on_creation(): void
    {
        $first = Link::factory()->create(['short_code' => null]);

        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{6}$/', $first->short_code);

        $second = Link::factory()->create(['short_code' => null]);
        $this->assertNotSame($first->short_code, $second->short_code);
    }
}
