<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUserCommand extends Command
{
    protected $signature = 'admin:create
        {email : The admin email address}
        {password : The admin password}
        {--first_name=Admin : First name for the admin account}
        {--last_name=User : Last name for the admin account}
        {--phone=09000000000 : Phone number for the admin account}
    ';

    protected $description = 'Create or update a Filament admin user account';

    public function handle(): int
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        $firstName = $this->option('first_name');
        $lastName = $this->option('last_name');
        $phone = $this->option('phone');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Please provide a valid email address.');

            return Command::FAILURE;
        }

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters long.');

            return Command::FAILURE;
        }

        $user = User::updateOrCreate([
            'email' => $email,
        ], [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone,
            'password' => Hash::make($password),
            'role' => 1,
            'status' => 'active',
            'email_verified_at' => now(),
            'registration_complete' => true,
            'terms_accepted' => true,
        ]);

        $user->wallet()->firstOrCreate([
            'user_id' => $user->id,
        ], [
            'balance' => 0,
            'currency' => 'NGN',
            'status' => 'active',
        ]);

        $this->info('Admin account created or updated successfully.');
        $this->line('Email: '.$email);
        $this->line('Password: '.$password);
        $this->line('Role: admin');
        $this->line('Filament panel URL: /admin');

        return Command::SUCCESS;
    }
}
