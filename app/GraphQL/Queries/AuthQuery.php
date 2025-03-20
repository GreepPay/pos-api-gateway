<?php declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\User\User;
use Illuminate\Support\Facades\Auth;

final class AuthQuery
{
    public function authUser()
    {
        if (Auth::user()) {
            return User::where("id", Auth::user()->id)->first();
        }

        return null;
    }
}
