<?php

declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Temp.
 *
 * @property string $key
 * @property string $value
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Temp newModelQuery()
 * @method static Builder|Temp newQuery()
 * @method static Builder|Temp query()
 * @method static Builder|Temp whereCreatedAt($value)
 * @method static Builder|Temp whereId($value)
 * @method static Builder|Temp whereKey($value)
 * @method static Builder|Temp whereUpdatedAt($value)
 * @method static Builder|Temp whereValue($value)
 * @mixin Eloquent
 */
class Temp extends Model
{
    protected $table = 'temps';

    public $timestamps = true;
}
