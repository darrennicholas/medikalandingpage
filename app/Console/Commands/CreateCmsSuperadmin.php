<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

#[Signature('cms:create-superadmin {name} {email}')]
#[Description('Create the first active CMS superadmin.')]
class CreateCmsSuperadmin extends Command
{
    public function handle(): int
    {
        $name = (string) $this->argument('name');
        $email = strtolower((string) $this->argument('email'));

        if (User::query()->where('email', $email)->exists()) {
            $this->components->error('Email sudah terdaftar.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Password awal');
        $confirmation = (string) $this->secret('Konfirmasi password');
        validator(
            ['password' => $password, 'password_confirmation' => $confirmation],
            ['password' => ['required', 'confirmed', Password::min((int) config('cms.authentication.password_min_length'))->mixedCase()->numbers()]],
        )->validate();

        $role = Role::query()->where('name', 'superadmin')->firstOrFail();
        User::query()->create([
            'name' => $name,
            'email' => $email,
            'password_hash' => Hash::make($password),
            'role_id' => $role->id,
            'status' => 'active',
            'must_change_password' => true,
        ]);

        $this->components->info('Superadmin dibuat. Password wajib diganti setelah login pertama.');

        return self::SUCCESS;
    }
}
