<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\EpisodeFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as BelongsToThroughTrait;

/**
 * App\Models\Episode.
 *
 * @property int    $thetvdb_id
 * @property int    $tmdb_id
 * @property int    $numero
 * @property string $name
 * @property string $name_fr
 * @property string $resume
 * @property string $resume_fr
 * @property string $particularite
 * @property string $diffusion_us
 * @property string $diffusion_fr
 * @property string $ba
 * @property float  $moyenne
 * @property int    $nbnotes
 * @property int    $season_id
 * @property int $id
 * @property string|null $picture
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Article[] $articles
 * @property-read int|null $articles_count
 * @property-read Collection|Artist[] $artists
 * @property-read int|null $artists_count
 * @property-read Collection|Comment[] $comments
 * @property-read int|null $comments_count
 * @property-read Collection|Artist[] $directors
 * @property-read int|null $directors_count
 * @property-read Collection|Artist[] $guests
 * @property-read int|null $guests_count
 * @property-read Season $season
 * @property-read Collection|User[] $users
 * @property-read int|null $users_count
 * @property-read Collection|Artist[] $writers
 * @property-read int|null $writers_count
 * @method static EpisodeFactory factory(...$parameters)
 * @method static Builder|Episode newModelQuery()
 * @method static Builder|Episode newQuery()
 * @method static Builder|Episode query()
 * @method static Builder|Episode whereBa($value)
 * @method static Builder|Episode whereCreatedAt($value)
 * @method static Builder|Episode whereDiffusionFr($value)
 * @method static Builder|Episode whereDiffusionUs($value)
 * @method static Builder|Episode whereId($value)
 * @method static Builder|Episode whereMoyenne($value)
 * @method static Builder|Episode whereName($value)
 * @method static Builder|Episode whereNameFr($value)
 * @method static Builder|Episode whereNbnotes($value)
 * @method static Builder|Episode whereNumero($value)
 * @method static Builder|Episode wherePicture($value)
 * @method static Builder|Episode whereResume($value)
 * @method static Builder|Episode whereResumeFr($value)
 * @method static Builder|Episode whereSeasonId($value)
 * @method static Builder|Episode whereThetvdbId($value)
 * @method static Builder|Episode whereTmdbId($value)
 * @method static Builder|Episode whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Episode extends Model
{
    use BelongsToThroughTrait;
    use HasFactory;

    protected $table = 'episodes';

    public $timestamps = true;

    protected $fillable = [
        'thetvdb_id',
        'tmdb_id',
        'numero',
        'name',
        'name_fr',
        'resume',
        'resume_fr',
        'diffusion_us',
        'diffusion_fr',
        'ba',
        'moyenne',
        'nbnotes',
        'picture',
    ];

    public function show(): BelongsToThrough
    {
        return $this->belongsToThrough(Show::class, Season::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo('App\Models\Season');
    }

    public function artists(): MorphToMany
    {
        return $this->morphToMany('App\Models\Artist', 'artistable');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany('App\Models\Comment', 'commentable');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User')->withPivot('rate', 'created_at', 'updated_at');
    }

    public function articles(): MorphToMany
    {
        return $this->morphToMany('App\Models\Article', 'articlable');
    }

    public function directors(): MorphToMany
    {
        return $this->morphToMany('App\Models\Artist', 'artistable')->wherePivot('profession', 'director');
    }

    public function writers(): MorphToMany
    {
        return $this->morphToMany('App\Models\Artist', 'artistable')->wherePivot('profession', 'writer');
    }

    public function guests(): MorphToMany
    {
        return $this->morphToMany('App\Models\Artist', 'artistable')->wherePivot('profession', 'guest');
    }
}
