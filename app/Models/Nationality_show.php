<?php

declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Nationality_show.
 *
 * @property int $nationality_id
 * @property int $show_id
 * @method static Builder|Nationality_show newModelQuery()
 * @method static Builder|Nationality_show newQuery()
 * @method static Builder|Nationality_show query()
 * @method static Builder|Nationality_show whereNationalityId($value)
 * @method static Builder|Nationality_show whereShowId($value)
 * @mixin Eloquent
 */
class Nationality_show extends Model
{
    protected $table = 'nationality_show';

    public $timestamps = false;
}
