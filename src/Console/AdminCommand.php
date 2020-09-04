<?php

namespace Canvas\Console;

use Canvas\Models\Role;
use Canvas\Models\UserMeta;
use Illuminate\Console\Command;
use Illuminate\Foundation\Auth\User;

class AdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'canvas:admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant admin access to a specific user';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $email = $this->ask('Enter the email of the user to grant admin access to');

        if (! $email) {
            $this->error('Please enter a valid email.');

            return;
        }

        $user = resolve(config('canvas.user', User::class))
            ->where('email', $email)
            ->first();

        if (! $user) {
            $this->error('Unable to find a user with that email.');

            return;
        }

        $meta = UserMeta::where('user_id', $user->id)->first() ?? new UserMeta(['user_id' => $user->id]);

        if ($meta->role_id === Role::ADMIN) {
            $this->info('User is already an admin.');

            return;
        }

        $meta->role_id = Role::ADMIN;

        $meta->save();

        $this->info('Access granted.');
    }
}