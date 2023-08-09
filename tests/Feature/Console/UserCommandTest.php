<?php

namespace NovaKit\NovaOnVapor\Tests\Feature\Console;

use Illuminate\Support\Facades\Auth;
use NovaKit\NovaOnVapor\Tests\TestCase;

class UserCommandTest extends TestCase
{
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
