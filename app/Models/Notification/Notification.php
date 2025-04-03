<?php

namespace App\Models\Notification;

use App\Exceptions\GraphQLException;
use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;
use MichaelAChrisco\ReadOnly\ReadOnlyTrait;
use Illuminate\Support\Facades\Auth;

/**
 *
 *
 * @property int $id
 * @property string $auth_user_id
 * @property string $type
 * @property string $title
 * @property string $content
 * @property string|null $email
 * @property bool $is_read
 * @property string $delivery_status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereAuthUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereDeliveryStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Notification extends Model
{
    use ReadOnlyTrait;

    protected $connection = "greep-notification";

    protected $table = "notifications";

    public function user(): User|null
    {
        return User::query()->where("id", $this->auth_user_id)->first();
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
        return $query->where("auth_user_id", Auth::id());
    }
}
