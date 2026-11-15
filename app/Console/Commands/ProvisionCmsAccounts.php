<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

#[Signature('cms:provision-accounts')]
#[Description('Create initial Superadmin, Admin, and public User accounts with hidden password prompts.')]
class ProvisionCmsAccounts extends Command
{
    public function handle(): int
    {
        $definitions = [
            ['role' => 'superadmin', 'name' => 'Superadmin', 'email' => 'superadmin@medikalandingpage.test', 'must_change_password' => true],
            ['role' => 'admin', 'name' => 'Admin Digital Marketing', 'email' => 'admin@medikalandingpage.test', 'must_change_password' => true],
            ['role' => 'user', 'name' => 'User Publik', 'email' => 'user@medikalandingpage.test', 'must_change_password' => false],
        ];
        $preparedAccounts = [];

        foreach ($definitions as $definition) {
            $role = Role::query()->where('name', $definition['role'])->first();

            if ($role === null) {
                $this->components->error('Role belum tersedia. Jalankan php artisan db:seed --class=RoleSeeder terlebih dahulu.');

                return self::FAILURE;
            }

            $email = strtolower(trim((string) $this->ask('Email untuk '.$definition['name'], $definition['email'])));

            if (User::query()->where('email', $email)->exists()) {
                $this->components->warn('Akun '.$email.' sudah ada; dilewati.');

                continue;
            }

            if (isset($preparedAccounts[$email])) {
                $this->components->error('Email harus unik untuk setiap akun awal.');

                return self::FAILURE;
            }

            $name = trim((string) $this->ask('Nama akun', $definition['name']));
            $password = (string) $this->secret('Password awal untuk '.$email);
            $passwordConfirmation = (string) $this->secret('Konfirmasi password');
            $validator = Validator::make(
                [
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'password_confirmation' => $passwordConfirmation,
                ],
                [
                    'name' => ['required', 'string', 'max:100'],
                    'email' => ['required', 'email', 'max:150', 'unique:users,email'],
                    'password' => ['required', 'confirmed', Password::min((int) config('cms.authentication.password_min_length'))->mixedCase()->numbers()],
                ],
            );

            if ($validator->fails()) {
                $this->components->error($validator->errors()->first());

                return self::FAILURE;
            }

            $preparedAccounts[$email] = [
                'name' => $name,
                'email' => $email,
                'password_hash' => Hash::make($password),
                'role_id' => $role->id,
                'status' => 'active',
                'must_change_password' => $definition['must_change_password'],
            ];
        }

        DB::transaction(function () use ($preparedAccounts): void {
            foreach ($preparedAccounts as $account) {
                User::query()->create($account);
            }
        });

        if ($preparedAccounts === []) {
            $this->components->info('Tidak ada akun baru; akun awal yang diminta sudah tersedia.');

            return self::SUCCESS;
        }

        $this->components->info('Akun awal berhasil dibuat. Password tidak ditampilkan dan tersimpan sebagai hash.');
        $this->components->warn('Role User hanya untuk akses publik dan tidak dapat masuk ke Admin CMS.');

        return self::SUCCESS;
    }
}
