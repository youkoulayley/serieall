<?php

declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Channel_show.
 *
 * @property int $channel_id
 * @property int $show_id
 * @method static Builder|Channel_show newModelQuery()
 * @method static Builder|Channel_show newQuery()
 * @method static Builder|Channel_show query()
 * @method static Builder|Channel_show whereChannelId($value)
 * @method static Builder|Channel_show whereShowId($value)
 * @mixin Eloquent
 */
class Channel_show extends Model
{
    protected $table = 'channel_show';

    public $timestamps = false;
}
