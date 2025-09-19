<?php
namespace App\Services\Auth;

use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterAccountService
{
    public function handle(string $accountName, string $name, string $email, string $password): array
    {
        return DB::transaction(function () use ($accountName, $name, $email, $password) {
            $account = Account::create([
                'name'   => $accountName,
                'slug'   => Str::slug($accountName) . '-' . Str::lower(Str::random(5)),
                'plan'   => 'free',
                'status' => 'active',
            ]);

            $user = User::create([
                'name'     => $name,
                'email'    => $email,
                'password' => Hash::make($password),
            ]);

            $account->users()->attach($user->id, [
                'role'     => 'owner',
                'is_owner' => true,
                'status'   => 'active',
            ]);

            return [$account, $user];
        });
    }
}
