<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Show_user.
 *
 * @property int $show_id
 * @property int $user_id
 * @property int $state
 * @property string|null $message
 * @method static Builder|Show_user newModelQuery()
 * @method static Builder|Show_user newQuery()
 * @method static Builder|Show_user query()
 * @method static Builder|Show_user whereMessage($value)
 * @method static Builder|Show_user whereShowId($value)
 * @method static Builder|Show_user whereState($value)
 * @method static Builder|Show_user whereUserId($value)
 * @mixin \Eloquent
 */
class Show_user extends Model
{
    protected $table = 'show_user';

    public $timestamps = false;

    protected $fillable = [
        'show_id',
        'user_id',
        'state',
        'message',
    ];
}
