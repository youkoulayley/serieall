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
 * App\Models\Nationality.
 *
 * @property string $name
 * @property string $nationality_url
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Show[] $shows
 * @property-read int|null $shows_count
 * @method static Builder|Nationality newModelQuery()
 * @method static Builder|Nationality newQuery()
 * @method static Builder|Nationality query()
 * @method static Builder|Nationality whereCreatedAt($value)
 * @method static Builder|Nationality whereId($value)
 * @method static Builder|Nationality whereName($value)
 * @method static Builder|Nationality whereNationalityUrl($value)
 * @method static Builder|Nationality whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Nationality extends Model
{
    protected $table = 'nationalities';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'nationality_url',
    ];

    public function shows(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Show');
    }
}
