<?php

declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Answer.
 *
 * @property string $name
 * @property int    $question_id
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Question $question
 * @method static Builder|Answer newModelQuery()
 * @method static Builder|Answer newQuery()
 * @method static Builder|Answer query()
 * @method static Builder|Answer whereCreatedAt($value)
 * @method static Builder|Answer whereId($value)
 * @method static Builder|Answer whereName($value)
 * @method static Builder|Answer whereQuestionId($value)
 * @method static Builder|Answer whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Answer extends Model
{
    protected $table = 'answers';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'question_id',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo('App\Models\Question');
    }
}
