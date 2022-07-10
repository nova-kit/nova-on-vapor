<?php

namespace NovaKit\NovaOnVapor\Tests\Feature\Console;

use Illuminate\Foundation\Auth\User;
use NovaKit\NovaOnVapor\Tests\TestCase;

class UserCommandTest extends TestCase
{
    /** @test */
    public function it_can_create_user()
    {
        $this->withoutMockingConsoleOutput();

        ray('start');

        $this->artisan('nova:vapor-user', [
            '--name' => 'Taylor Otwell',
            '--email' => 'taylor@laravel.com',
            '--password' => 'secret',
            '--no-interaction' => true,
        ]);

        ray(User::all());
    }
}
