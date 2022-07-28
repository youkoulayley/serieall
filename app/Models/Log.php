<?php

declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Log.
 *
 * @property int    $list_log_id
 * @property string $message
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read List_log $list_log
 * @method static Builder|Log newModelQuery()
 * @method static Builder|Log newQuery()
 * @method static Builder|Log query()
 * @method static Builder|Log whereCreatedAt($value)
 * @method static Builder|Log whereId($value)
 * @method static Builder|Log whereListLogId($value)
 * @method static Builder|Log whereMessage($value)
 * @method static Builder|Log whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Log extends Model
{
    protected $table = 'logs';

    public $timestamps = true;

    protected $fillable = [
        'list_log_id',
        'message',
    ];

    public function list_log(): BelongsTo
    {
        return $this->belongsTo('App\Models\List_log');
    }
}
