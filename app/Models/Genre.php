<?php

declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Genre.
 *
 * @property string $name
 * @property string $genre_url
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Show[] $shows
 * @property-read int|null $shows_count
 * @method static Builder|Genre newModelQuery()
 * @method static Builder|Genre newQuery()
 * @method static Builder|Genre query()
 * @method static Builder|Genre whereCreatedAt($value)
 * @method static Builder|Genre whereGenreUrl($value)
 * @method static Builder|Genre whereId($value)
 * @method static Builder|Genre whereName($value)
 * @method static Builder|Genre whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Genre extends Model
{
    protected $table = 'genres';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'genre_url',
    ];

    public function shows(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Show');
    }
}
