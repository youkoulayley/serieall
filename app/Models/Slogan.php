<?php

declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Slogan.
 *
 * @property string $message
 * @property string $source
 * @property string $url
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Slogan newModelQuery()
 * @method static Builder|Slogan newQuery()
 * @method static Builder|Slogan query()
 * @method static Builder|Slogan whereCreatedAt($value)
 * @method static Builder|Slogan whereId($value)
 * @method static Builder|Slogan whereMessage($value)
 * @method static Builder|Slogan whereSource($value)
 * @method static Builder|Slogan whereUpdatedAt($value)
 * @method static Builder|Slogan whereUrl($value)
 * @mixin Eloquent
 */
class Slogan extends Model
{
    protected $table = 'slogans';

    public $timestamps = true;

    protected $fillable = [
        'message',
        'source',
        'url',
    ];
}
