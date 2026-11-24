<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicStorageFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_serves_a_file_from_the_public_disk_without_a_symlink(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('homepage-banner/banner.txt', 'public banner');

        $response = $this->get('/storage/homepage-banner/banner.txt');

        $response->assertOk()
            ->assertHeader('cache-control', 'max-age=86400, public')
            ->assertHeader('x-content-type-options', 'nosniff');
        $this->assertSame('public banner', $response->streamedContent());
    }

    public function test_it_returns_not_found_for_missing_or_unsafe_paths(): void
    {
        Storage::fake('public');

        $this->get('/storage/homepage-banner/missing.png')->assertNotFound();
        $this->get('/storage/%2e%2e/.env')->assertNotFound();
    }
}
