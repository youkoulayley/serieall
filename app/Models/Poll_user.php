<?php

declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Poll_user.
 *
 * @property int $poll_id
 * @property int $user_id
 * @method static Builder|Poll_user newModelQuery()
 * @method static Builder|Poll_user newQuery()
 * @method static Builder|Poll_user query()
 * @method static Builder|Poll_user wherePollId($value)
 * @method static Builder|Poll_user whereUserId($value)
 * @mixin Eloquent
 */
class Poll_user extends Model
{
    protected $table = 'poll_user';

    public $timestamps = false;

    protected $fillable = [
        'poll_id',
        'user_id',
    ];
}
