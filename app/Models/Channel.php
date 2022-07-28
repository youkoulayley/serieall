<?php

declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Channel.
 *
 * @property string $name
 * @property string $channel_url
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Article[] $articles
 * @property-read int|null $articles_count
 * @property-read Collection|Show[] $shows
 * @property-read int|null $shows_count
 * @method static Builder|Channel newModelQuery()
 * @method static Builder|Channel newQuery()
 * @method static Builder|Channel query()
 * @method static Builder|Channel whereChannelUrl($value)
 * @method static Builder|Channel whereCreatedAt($value)
 * @method static Builder|Channel whereId($value)
 * @method static Builder|Channel whereName($value)
 * @method static Builder|Channel whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Channel extends Model
{
    protected $table = 'channels';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'channel_url',
    ];

    public function shows(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Show');
    }

    public function articles(): MorphToMany
    {
        return $this->morphToMany('App\Models\Article', 'articlable');
    }
}
