<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ShowFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * Model for a TV Show.
 *
 * @property int $id
 * @property int|null $thetvdb_id
 * @property string $show_url
 * @property string $name
 * @property string|null $name_fr
 * @property string|null $synopsis
 * @property string|null $synopsis_fr
 * @property int $format
 * @property int|null $annee
 * @property int $encours
 * @property string|null $diffusion_us
 * @property string|null $diffusion_fr
 * @property string|null $particularite
 * @property float $moyenne
 * @property float $moyenne_redac
 * @property int $nbnotes
 * @property int|null $taux_erectile
 * @property string|null $avis_rentree
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $tmdb_id
 * @property-read Collection|Artist[] $actors
 * @property-read int|null $actors_count
 * @property-read Collection|Article[] $articles
 * @property-read int|null $articles_count
 * @property-read Collection|Artist[] $artists
 * @property-read int|null $artists_count
 * @property-read Collection|Channel[] $channels
 * @property-read int|null $channels_count
 * @property-read Collection|Comment[] $comments
 * @property-read int|null $comments_count
 * @property-read Collection|Artist[] $creators
 * @property-read int|null $creators_count
 * @property-read Collection|Episode[] $episodes
 * @property-read int|null $episodes_count
 * @property-read Collection|Genre[] $genres
 * @property-read int|null $genres_count
 * @property-read Collection|Nationality[] $nationalities
 * @property-read int|null $nationalities_count
 * @property-read Collection|Season[] $seasons
 * @property-read int|null $seasons_count
 * @property-read Collection|User[] $users
 * @property-read int|null $users_count
 * @method static ShowFactory factory(...$parameters)
 * @method static Builder|Show newModelQuery()
 * @method static Builder|Show newQuery()
 * @method static Builder|Show query()
 * @method static Builder|Show whereAnnee($value)
 * @method static Builder|Show whereAvisRentree($value)
 * @method static Builder|Show whereCreatedAt($value)
 * @method static Builder|Show whereDiffusionFr($value)
 * @method static Builder|Show whereDiffusionUs($value)
 * @method static Builder|Show whereEncours($value)
 * @method static Builder|Show whereFormat($value)
 * @method static Builder|Show whereId($value)
 * @method static Builder|Show whereMoyenne($value)
 * @method static Builder|Show whereMoyenneRedac($value)
 * @method static Builder|Show whereName($value)
 * @method static Builder|Show whereNameFr($value)
 * @method static Builder|Show whereNbnotes($value)
 * @method static Builder|Show whereParticularite($value)
 * @method static Builder|Show whereShowUrl($value)
 * @method static Builder|Show whereSynopsis($value)
 * @method static Builder|Show whereSynopsisFr($value)
 * @method static Builder|Show whereTauxErectile($value)
 * @method static Builder|Show whereThetvdbId($value)
 * @method static Builder|Show whereTmdbId($value)
 * @method static Builder|Show whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Show extends Model
{
    use HasRelationships;
    use HasEagerLimit;
    use HasFactory;

    protected $table = 'shows';

    public $timestamps = true;

    protected $fillable = [
        'thetvdb_id',
        'tmdb_id',
        'show_url',
        'name',
        'name_fr',
        'synopsis',
        'synopsis_fr',
        'format',
        'annee',
        'encours',
        'diffusion_us',
        'diffusion_fr',
        'particularite',
        'moyenne',
        'moyenne_redac',
        'nbnotes',
        'taux_erectile',
        'avis_rentree',
    ];

    /**
     * Return linked seasons.
     */
    public function seasons(): HasMany
    {
        return $this->hasMany('App\Models\Season')->orderBy('name');
    }

    /**
     * Return linked episodes.
     */
    public function episodes(): HasManyThrough
    {
        return $this->hasManyThrough('App\Models\Episode', '\App\Models\Season');
    }

    /**
     * Return linked artists.
     */
    public function artists(): MorphToMany
    {
        return $this->morphToMany('App\Models\Artist', 'artistable');
    }

    /**
     * Return linked channels.
     */
    public function channels(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Channel');
    }

    /**
     * Return linked nationalities.
     */
    public function nationalities(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Nationality');
    }

    /**
     * Return linked genres.
     */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Genre');
    }

    /**
     * Return linked comments.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany('App\Models\Comment', 'commentable');
    }

    /**
     * Return linked articles.
     */
    public function articles(): MorphToMany
    {
        return $this->morphToMany('App\Models\Article', 'articlable');
    }

    /**
     * Return linked creators.
     */
    public function creators(): BelongsToMany
    {
        return $this->morphToMany('App\Models\Artist', 'artistable')->wherePivot('profession', 'creator');
    }

    /**
     * Return linked actors.
     */
    public function actors(): MorphToMany
    {
        return $this->morphToMany('App\Models\Artist', 'artistable')->orderBy('name')->wherePivot('profession', 'actor')->withPivot('role');
    }

    /**
     * Return users following the show.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User')->withPivot('state', 'message');
    }

    /**
     * Return linked episodes rates.
     */
    public function rates(): HasManyDeep
    {
        return $this->hasManyDeep('App\Models\Episode_user', ['App\Models\Season', 'App\Models\Episode'], [null]);
    }
}
