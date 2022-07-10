<?php

namespace NovaKit\NovaOnVapor\Tests\Feature\Console;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use NovaKit\NovaOnVapor\Tests\TestCase;

class UserCommandTest extends TestCase
{
    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
    }

    /** @test */
    public function it_can_create_user()
    {
        $this->withoutMockingConsoleOutput();

        $this->artisan('nova:vapor-user', [
            '--name' => 'Taylor Otwell',
            '--email' => 'taylor@laravel.com',
            '--password' => 'secret',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
        ]);

        $this->assertTrue(Auth::attempt(['email' => 'taylor@laravel.com', 'password' => 'secret']));
    }
}
