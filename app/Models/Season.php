<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SeasonFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Season.
 *
 * @property int $id
 * @property int|null $thetvdb_id
 * @property int $name
 * @property string|null $ba
 * @property float $moyenne
 * @property int $nbnotes
 * @property int $show_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $tmdb_id
 * @property-read Collection|Article[] $articles
 * @property-read int|null $articles_count
 * @property-read Collection|Comment[] $comments
 * @property-read int|null $comments_count
 * @property-read Collection|Episode[] $episodes
 * @property-read int|null $episodes_count
 * @property-read Show $show
 * @property-read Collection|Episode_user[] $users
 * @property-read int|null $users_count
 * @method static SeasonFactory factory(...$parameters)
 * @method static Builder|Season newModelQuery()
 * @method static Builder|Season newQuery()
 * @method static Builder|Season query()
 * @method static Builder|Season whereBa($value)
 * @method static Builder|Season whereCreatedAt($value)
 * @method static Builder|Season whereId($value)
 * @method static Builder|Season whereMoyenne($value)
 * @method static Builder|Season whereName($value)
 * @method static Builder|Season whereNbnotes($value)
 * @method static Builder|Season whereShowId($value)
 * @method static Builder|Season whereThetvdbId($value)
 * @method static Builder|Season whereTmdbId($value)
 * @method static Builder|Season whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Season extends Model
{
    use HasFactory;

    protected $table = 'seasons';

    public $timestamps = true;

    protected $fillable = [
        'thetvdb_id',
        'tmdb_id',
        'name',
        'ba',
        'moyenne',
        'nbnotes',
    ];

    public function show(): BelongsTo
    {
        return $this->belongsTo('App\Models\Show');
    }

    public function episodes(): HasMany
    {
        return $this->hasMany('App\Models\Episode')
            ->orderBy('diffusion_us')
            ->orderBy('numero');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany('App\Models\Comment', 'commentable');
    }

    public function articles(): MorphToMany
    {
        return $this->morphToMany('App\Models\Article', 'articlable');
    }

    public function users(): HasManyThrough
    {
        return $this->hasManyThrough('App\Models\Episode_user', 'App\Models\Episode');
    }
}
