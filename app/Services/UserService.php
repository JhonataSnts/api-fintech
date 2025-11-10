<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserService
{
    public function getAllExceptAuthUser()
    {
        $authUser = Auth::user();

        return User::where('id', '!=', $authUser->id)
            ->select('id', 'nome', 'email')
            ->get();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user;
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}