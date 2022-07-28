<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * App\Models\User.
 *
 * @property int    $id
 * @property string $username
 * @property string $user_url
 * @property string $email
 * @property bool   $role
 * @property bool   $suspended
 * @property bool   $activated
 * @property string $edito
 * @property bool   $antispoiler
 * @property string $website
 * @property string $twitter
 * @property string $facebook
 * @property string $ip
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Article[] $articles
 * @property-read int|null $articles_count
 * @property-read Collection|Comment[] $comments
 * @property-read int|null $comments_count
 * @property-read Collection|Episode[] $episodes
 * @property-read int|null $episodes_count
 * @property-read Collection|List_log[] $logs
 * @property-read int|null $logs_count
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection|Poll[] $polls
 * @property-read int|null $polls_count
 * @property-read Collection|Episode[] $rates
 * @property-read int|null $rates_count
 * @property-read Collection|Show[] $shows
 * @property-read int|null $shows_count
 * @method static UserFactory factory(...$parameters)
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereActivated($value)
 * @method static Builder|User whereAntispoiler($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEdito($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereFacebook($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereIp($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereRole($value)
 * @method static Builder|User whereSuspended($value)
 * @method static Builder|User whereTwitter($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereUserUrl($value)
 * @method static Builder|User whereUsername($value)
 * @method static Builder|User whereWebsite($value)
 * @mixin Eloquent
 */
class User extends Authenticatable
{
    use Notifiable;
    use HasFactory;

    protected $table = 'users';

    public $timestamps = true;

    protected $fillable = [
        'username',
        'user_url',
        'email',
        'password',
        'role',
        'suspended',
        'activated',
        'edito',
        'antispoiler',
        'website',
        'twitter',
        'facebook',
        'ip',
        'rememberToken',
    ];

    protected $hidden = [
        'password',
        'rememberToken',
    ];

    public function comments(): HasMany
    {
        return $this->hasMany('App\Models\Comment');
    }

    public function shows(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Show')->withPivot('state', 'message');
    }

    public function episodes(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Episode')->withPivot('rate', 'updated_at');
    }

    public function rates(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Episode');
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Article');
    }

    public function polls(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Poll');
    }

    public function logs(): HasMany
    {
        return $this->hasMany('App\Models\List_log');
    }
}
