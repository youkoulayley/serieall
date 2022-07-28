<?php

declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Artist.
 *
 * @property string $name
 * @property string $artist_url
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Article[] $articles
 * @property-read int|null $articles_count
 * @property-read Collection|Episode[] $episodes
 * @property-read int|null $episodes_count
 * @property-read Collection|Show[] $shows
 * @property-read int|null $shows_count
 * @method static Builder|Artist newModelQuery()
 * @method static Builder|Artist newQuery()
 * @method static Builder|Artist query()
 * @method static Builder|Artist whereArtistUrl($value)
 * @method static Builder|Artist whereCreatedAt($value)
 * @method static Builder|Artist whereId($value)
 * @method static Builder|Artist whereName($value)
 * @method static Builder|Artist whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Artist extends Model
{
    protected $table = 'artists';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'artist_url',
    ];

    public function episodes(): MorphToMany
    {
        return $this->morphedByMany('App\Models\Episode', 'artistable');
    }

    public function shows(): MorphToMany
    {
        return $this->morphedByMany('App\Models\Show', 'artistable');
    }

    public function articles(): MorphToMany
    {
        return $this->morphToMany('App\Models\Article', 'articlable');
    }
}
