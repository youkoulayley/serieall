<?php

declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Poll.
 *
 * @property string $name
 * @property string $poll_url
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Question[] $questions
 * @property-read int|null $questions_count
 * @property-read Collection|User[] $users
 * @property-read int|null $users_count
 * @method static Builder|Poll newModelQuery()
 * @method static Builder|Poll newQuery()
 * @method static Builder|Poll query()
 * @method static Builder|Poll whereCreatedAt($value)
 * @method static Builder|Poll whereId($value)
 * @method static Builder|Poll whereName($value)
 * @method static Builder|Poll wherePollUrl($value)
 * @method static Builder|Poll whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Poll extends Model
{
    protected $table = 'polls';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'poll_url',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany('App\Models\Question');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User');
    }
}
