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
 * App\Models\List_log.
 *
 * @property string $job
 * @property string $object
 * @property int    $object_id
 * @property int    $user_id
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Log[] $logs
 * @property-read int|null $logs_count
 * @property-read User|null $user
 * @method static Builder|List_log newModelQuery()
 * @method static Builder|List_log newQuery()
 * @method static Builder|List_log query()
 * @method static Builder|List_log whereCreatedAt($value)
 * @method static Builder|List_log whereId($value)
 * @method static Builder|List_log whereJob($value)
 * @method static Builder|List_log whereObject($value)
 * @method static Builder|List_log whereObjectId($value)
 * @method static Builder|List_log whereUpdatedAt($value)
 * @method static Builder|List_log whereUserId($value)
 * @mixin Eloquent
 */
class List_log extends Model
{
    protected $table = 'list_logs';

    public $timestamps = true;

    protected $fillable = [
        'job',
        'object',
        'object_id',
        'user_id',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany('App\Models\Log');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }
}
