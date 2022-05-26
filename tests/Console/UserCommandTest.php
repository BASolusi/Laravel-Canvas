<?php

namespace Canvas\Tests\Console;

use Canvas\Models\User;
use Canvas\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class AdminCommandTest.
 *
 * @covers \Canvas\Console\UserCommand
 */
class UserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function testCanvasUserCommandWillValidateAnEmptyEmail(): void
    {
        $this->artisan('canvas:user admin')
             ->expectsQuestion('What email should be attached to the user?', '')
             ->assertExitCode(0)
             ->expectsOutput('Please enter a valid email.');
    }

    public function testCanvasUserCommandWillValidateAnInvalidEmail(): void
    {
        $this->artisan('canvas:user admin')
             ->expectsQuestion('What email should be attached to the user?', 'bad.email')
             ->assertExitCode(0)
             ->expectsOutput('Please enter a valid email.');
    }

    public function testCanvasUserCommandWillValidateAnInvalidRole(): void
    {
        $this->artisan('canvas:user ad')
             ->assertExitCode(0)
             ->expectsOutput('Please enter a valid role. (contributor,editor,admin)');
    }

    public function testCanvasUserCommandCanCreateANewContributor(): void
    {
        $this->artisan('canvas:user contributor')
             ->expectsQuestion('What email should be attached to the user?', 'contributor@example.com')
             ->assertExitCode(0)
             ->expectsOutput('New user created.');

        $this->assertDatabaseHas('canvas_users', [
            'email' => 'contributor@example.com',
            'role' => User::$contributor,
        ]);
    }

    public function testCanvasUserCommandCanCreateANewEditor(): void
    {
        $this->artisan('canvas:user editor')
             ->expectsQuestion('What email should be attached to the user?', 'editor@example.com')
             ->assertExitCode(0)
             ->expectsOutput('New user created.');

        $this->assertDatabaseHas('canvas_users', [
            'email' => 'editor@example.com',
            'role' => User::$editor,
        ]);
    }

    public function testCanvasUserCommandCanCreateANewAdmin(): void
    {
        $this->artisan('canvas:user admin')
             ->expectsQuestion('What email should be attached to the user?', 'admin@example.com')
             ->assertExitCode(0)
             ->expectsOutput('New user created.');

        $this->assertDatabaseHas('canvas_users', [
            'email' => 'admin@example.com',
            'role' => User::$admin,
        ]);
    }
}
