<?php

declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Artistable.
 *
 * @property int    $artist_id
 * @property int    $artistable_id
 * @property string $artistable_type
 * @property string $profession
 * @property string $role
 * @method static Builder|Artistable newModelQuery()
 * @method static Builder|Artistable newQuery()
 * @method static Builder|Artistable query()
 * @method static Builder|Artistable whereArtistId($value)
 * @method static Builder|Artistable whereArtistableId($value)
 * @method static Builder|Artistable whereArtistableType($value)
 * @method static Builder|Artistable whereProfession($value)
 * @method static Builder|Artistable whereRole($value)
 * @mixin Eloquent
 */
class Artistable extends Model
{
    protected $table = 'artistables';

    public $timestamps = false;
}
