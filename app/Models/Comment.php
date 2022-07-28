<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CommentFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Comment.
 *
 * @property int    $left
 * @property int    $right
 * @property string $message
 * @property string $thumb
 * @property bool   $spoiler
 * @property int    $user_id
 * @property int    $parent_id
 * @property int    $commentable_id
 * @property int    $commentable_type
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Comment[] $children
 * @property-read int|null $children_count
 * @property-read Model|Eloquent $commentable
 * @property-read Comment|null $parent
 * @property-read User $user
 * @method static CommentFactory factory(...$parameters)
 * @method static Builder|Comment newModelQuery()
 * @method static Builder|Comment newQuery()
 * @method static Builder|Comment query()
 * @method static Builder|Comment whereCommentableId($value)
 * @method static Builder|Comment whereCommentableType($value)
 * @method static Builder|Comment whereCreatedAt($value)
 * @method static Builder|Comment whereId($value)
 * @method static Builder|Comment whereMessage($value)
 * @method static Builder|Comment whereParentId($value)
 * @method static Builder|Comment whereSpoiler($value)
 * @method static Builder|Comment whereThumb($value)
 * @method static Builder|Comment whereUpdatedAt($value)
 * @method static Builder|Comment whereUserId($value)
 * @mixin Eloquent
 */
class Comment extends Model
{
    use HasFactory;

    protected $table = 'comments';

    public $timestamps = true;

    protected $fillable = [
        'message',
        'thumb',
        'spoiler',
        'parent_id',
        'commentable_id',
        'commentable_type',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    public function children(): HasMany
    {
        return $this->hasMany('App\Models\Comment', 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo('App\Models\Comment', 'parent_id');
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }
}
