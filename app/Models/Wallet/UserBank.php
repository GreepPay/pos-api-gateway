<?php

namespace App\Models\Wallet;

use App\Exceptions\GraphQLException;
use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;
use MichaelAChrisco\ReadOnly\ReadOnlyTrait;
use Illuminate\Support\Facades\Auth;

/**
 *
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property string $uuid
 * @property int $user_id
 * @property int $wallet_id
 * @property string $bank_code
 * @property string $bank_name
 * @property string $account_no
 * @property string $currency
 * @property bool $is_verified
 * @property string $state
 * @property string|null $meta_data
 * @property-read User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserBank newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBank newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBank query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBank whereAccountNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBank whereBankCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBank whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBank whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBank whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBank whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBank whereIsVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBank whereMetaData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBank whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBank whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBank whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBank whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBank whereWalletId($value)
 * @mixin \Eloquent
 */
class UserBank extends Model
{
    use ReadOnlyTrait;

    protected $connection = "greep-wallet";

    public function user(): User|null
    {
        return User::query()->where("id", $this->user_id)->first();
    }

    /**
     * Scope a query to only include point transactions belonging to the currently authenticated user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param bool $forCurrentUser
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCurrentUser($query, bool $forCurrentUser): mixed
    {
        if (!$forCurrentUser) {
            throw new GraphQLException("Unauthorized");
        }
        return $query->where("user_id", Auth::id());
    }
}
