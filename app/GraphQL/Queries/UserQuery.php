<?php declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

final class UserQuery
{
    public function authUser()
    {
        if (Auth::user()) {
            return User::where("id", Auth::user()->id)->first();
        }

        return null;
    }
}
