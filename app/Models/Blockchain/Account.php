<?php

namespace App\Models\Blockchain;

use App\Models\Wallet\Wallet;
use Illuminate\Database\Eloquent\Model;
use MichaelAChrisco\ReadOnly\ReadOnlyTrait;

/**
 *
 *
 * @property int $id
 * @property string $stellar_address
 * @property string $account_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Blockchain\Trustline> $trustlines
 * @property-read int|null $trustlines_count
 * @property-read Wallet|null $wallet
 * @method static \Illuminate\Database\Eloquent\Builder|Account newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Account newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Account query()
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereAccountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereStellarAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Account extends Model
{
    use ReadOnlyTrait;

    protected $connection = "greep-blockchain";

    protected $table = "accounts";

    public function wallet(): Wallet|null
    {
        return Wallet::query()
            ->where("blockchain_account_id", $this->id)
            ->first();
    }

    public function trustlines(): Trustline|null
    {
        return Trustline::query()->where("account_id", $this->id)->get();
    }
}
