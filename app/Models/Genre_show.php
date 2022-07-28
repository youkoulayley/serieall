<?php

declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Genre_show.
 *
 * @property int $genre_id
 * @property int $show_id
 * @method static Builder|Genre_show newModelQuery()
 * @method static Builder|Genre_show newQuery()
 * @method static Builder|Genre_show query()
 * @method static Builder|Genre_show whereGenreId($value)
 * @method static Builder|Genre_show whereShowId($value)
 * @mixin Eloquent
 */
class Genre_show extends Model
{
    protected $table = 'genre_show';

    public $timestamps = false;
}
