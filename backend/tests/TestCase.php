<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    protected function fromFrontend(): static
    {
        $frontend = config('app.frontend_url');

        return $this->withHeaders([
            'Origin' => $frontend,
            'Referer' => rtrim($frontend, '/').'/',
        ]);
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    protected function loginAsSpa(array $credentials): TestResponse
    {
        return $this->fromFrontend()->postJson('/api/v1/login', $credentials);
    }

    protected function actingAsUser(User $user): static
    {
        return $this->actingAs($user, 'web');
    }
}
