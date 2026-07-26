<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_redirect_route_redirects_to_google(): void
    {
        $response = $this->get(route('auth.google'));

        $response->assertRedirect();
        $this->assertStringContainsString('accounts.google.com', $response->getTargetUrl());
    }

    public function test_login_page_renders_google_sign_in_button(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee(route('auth.google'));
        $response->assertSee('Sign in with Google');
    }

    public function test_register_page_renders_google_sign_in_button(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
        $response->assertSee(route('auth.google'));
        $response->assertSee('Sign up with Google');
    }

    public function test_google_callback_creates_new_user_and_logs_in(): void
    {
        $abstractUser = $this->mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-123456');
        $abstractUser->shouldReceive('getName')->andReturn('Test User');
        $abstractUser->shouldReceive('getEmail')->andReturn('test@example.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');
        $abstractUser->shouldReceive('getNickname')->andReturn(null);

        Socialite::shouldReceive('driver->user')->andReturn($abstractUser);

        $response = $this->get(route('auth.google.callback'));
        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'google_id' => 'google-123456',
        ]);

        $this->assertAuthenticated();
    }

    public function test_google_callback_links_to_existing_user_by_email(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
            'google_id' => null,
        ]);

        $abstractUser = $this->mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-999');
        $abstractUser->shouldReceive('getEmail')->andReturn('existing@example.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://example.com/new-avatar.jpg');

        Socialite::shouldReceive('driver->user')->andReturn($abstractUser);

        $response = $this->get(route('auth.google.callback'));
        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'id' => $existingUser->id,
            'google_id' => 'google-999',
        ]);

        $this->assertAuthenticatedAs($existingUser);
    }
}
