<?php

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\withoutMockingConsoleOutput;

beforeEach(function () {
    withoutMockingConsoleOutput();
});

it('can create user via `nova:vapor-user` command', function () {
    artisan('nova:vapor-user', [
        '--name' => 'Taylor Otwell',
        '--email' => 'taylor@laravel.com',
        '--password' => 'secret',
    ]);

    assertDatabaseHas('users', [
        'name' => 'Taylor Otwell',
        'email' => 'taylor@laravel.com',
    ]);

    expect(
        Auth::attempt(['email' => 'taylor@laravel.com', 'password' => 'secret'])
    )->toBeTrue();
});
