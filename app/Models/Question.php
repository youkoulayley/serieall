<?php

declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Question.
 *
 * @property string $name
 * @property int    $poll_id
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Answer[] $answers
 * @property-read int|null $answers_count
 * @property-read Poll $poll
 * @method static Builder|Question newModelQuery()
 * @method static Builder|Question newQuery()
 * @method static Builder|Question query()
 * @method static Builder|Question whereCreatedAt($value)
 * @method static Builder|Question whereId($value)
 * @method static Builder|Question whereName($value)
 * @method static Builder|Question wherePollId($value)
 * @method static Builder|Question whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Question extends Model
{
    protected $table = 'questions';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'poll_id',
    ];

    public function poll(): BelongsTo
    {
        return $this->belongsTo('App\Models\Poll');
    }

    public function answers(): HasMany
    {
        return $this->hasMany('App\Models\Answer');
    }
}
