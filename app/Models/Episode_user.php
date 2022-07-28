<?php

declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;

/**
 * App\Models\Episode_user.
 *
 * @property int $episode_id
 * @property int $user_id
 * @property int $rate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Episode $episode
 * @property-read User $user
 * @method static Builder|Episode_user newModelQuery()
 * @method static Builder|Episode_user newQuery()
 * @method static Builder|Episode_user query()
 * @method static Builder|Episode_user whereCreatedAt($value)
 * @method static Builder|Episode_user whereEpisodeId($value)
 * @method static Builder|Episode_user whereRate($value)
 * @method static Builder|Episode_user whereUpdatedAt($value)
 * @method static Builder|Episode_user whereUserId($value)
 * @mixin Eloquent
 */
class Episode_user extends Model
{
    use HasEagerLimit, HasFactory;

    protected $table = 'episode_user';

    public $timestamps = true;

    protected $fillable = [
        'episode_id',
        'user_id',
        'rate',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function episode(): BelongsTo
    {
        return $this->belongsTo('App\Models\Episode', 'episode_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
