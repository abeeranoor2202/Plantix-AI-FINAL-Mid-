<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class UiUxConsistencyContractTest extends TestCase
{
    /** @test */
    public function all_primary_layouts_load_shared_platform_api_client(): void
    {
        $paths = [
            base_path('resources/views/layouts/app.blade.php'),
            base_path('resources/views/vendor/layouts/app.blade.php'),
            base_path('resources/views/expert/layouts/app.blade.php'),
            base_path('resources/views/partials/scripts.blade.php'),
        ];

        foreach ($paths as $path) {
            $this->assertTrue(File::exists($path), 'Missing layout file: ' . $path);
            $content = File::get($path);
            $this->assertStringContainsString("platform-api.js", $content, 'Missing API client include in: ' . $path);
        }
    }

    /** @test */
    public function shared_api_client_enforces_loading_and_feedback_patterns(): void
    {
        $path = base_path('public/js/platform-api.js');
        $this->assertTrue(File::exists($path));

        $content = File::get($path);

        $this->assertStringContainsString('Loading...', $content);
        $this->assertStringContainsString('toast-container', $content);
        $this->assertStringContainsString('text-bg-', $content);
        $this->assertStringContainsString("type === 'error' ? 'danger' : 'success'", $content);
    }
}
