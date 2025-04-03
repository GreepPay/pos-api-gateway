<?php

namespace App\Models\User;

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
 * @property string|null $location
 * @property string $license
 * @property string $vehicle_type
 * @property string $vehicle_registration_number
 * @property string|null $vehicle_insurance
 * @property string|null $experience_years
 * @property string $availability_status
 * @property string $notification_preferences
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property string|null $country
 * @property string|null $city
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Rider newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rider newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rider query()
 * @method static \Illuminate\Database\Eloquent\Builder|Rider whereAuthUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rider whereAvailabilityStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rider whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rider whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rider whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rider whereExperienceYears($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rider whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rider whereLicense($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rider whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rider whereNotificationPreferences($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rider whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rider whereVehicleInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rider whereVehicleRegistrationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rider whereVehicleType($value)
 * @mixin \Eloquent
 */
class Rider extends Model
{
    use ReadOnlyTrait;

    protected $connection = "greep-user";

    protected $table = "riders";

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
