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
 * @property string $dr_or_cr
 * @property int $wallet_id
 * @property int $user_id
 * @property string $amount
 * @property string $point_balance
 * @property string $charge_id
 * @property string $state
 * @property string $chargeable_type
 * @property string $description
 * @property string $status
 * @property string $reference
 * @property string|null $extra_data
 * @property string $currency
 * @property-read User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|PointTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PointTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PointTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|PointTransaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointTransaction whereChargeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointTransaction whereChargeableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointTransaction whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointTransaction whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointTransaction whereDrOrCr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointTransaction whereExtraData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointTransaction wherePointBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointTransaction whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointTransaction whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointTransaction whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointTransaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointTransaction whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointTransaction whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointTransaction whereWalletId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointTransaction forCurrentUser()
 * @mixin \Eloquent
 */
class PointTransaction extends Model
{
    use ReadOnlyTrait;
    protected $connection = "greep-wallet";

    protected $table = "point_transactions";

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
